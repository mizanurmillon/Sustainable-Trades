<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PaypalAccount;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class OnboardingController extends Controller
{
    use ApiResponse;

    public function onboard(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'success_url' => 'required|url',
        //     'cancel_url' => 'required|url',
        // ]);

        // if ($validator->fails()) {
        //     return $this->error($validator->errors(), 'Validation error', 400);
        // }
        $vendor = auth()->user();

        if ($vendor->paypalAccount == null) {

            $clientId = config('services.paypal.sandbox.client_id');
            $clientSecret = config('services.paypal.sandbox.client_secret');

            // Get access token
            $tokenResponse = Http::withBasicAuth($clientId, $clientSecret)
                ->asForm()
                ->post('https://api-m.sandbox.paypal.com/v1/oauth2/token', [
                    'grant_type' => 'client_credentials',
                ]);

            if (!$tokenResponse->successful()) {
                return $this->error([], 'Failed to get PayPal access token', 400);
            }

            $accessToken = $tokenResponse->json()['access_token'];

            // Generate tracking ID
            $trackingId = (string) Str::uuid();
            // Create Partner Referral
            $payload = [
                "tracking_id" => $trackingId,
                'user_id' => $vendor->id,
                "partner_config_override" => [
                    "return_url" => route('paypal.onboard.success', ['tracking_id' => $trackingId, 'user_id' => $vendor->id]),
                    // "cancel_url" => route('paypal.onboard.cancel', ['tracking_id' => $trackingId]),
                    "show_add_credit_card" => true,
                ],
                "operations" => [[
                    "operation" => "API_INTEGRATION",
                    "api_integration_preference" => [
                        "rest_api_integration" => [
                            "integration_method" => "PAYPAL",
                            "integration_type" => "THIRD_PARTY",
                            "third_party_details" => [
                                "features" => ["PAYMENT", "REFUND", "PARTNER_FEE"],
                            ],
                        ],
                    ],
                ]],
                "products" => ["EXPRESS_CHECKOUT"],
                "legal_consents" => [[
                    "type" => "SHARE_DATA_CONSENT",
                    "granted" => true,
                ]],

            ];

            // dd($payload);

            $response = Http::withToken($accessToken)
                ->post('https://api-m.sandbox.paypal.com/v2/customer/partner-referrals', $payload);

            if (!$response->successful()) {
                Log::error('PayPal Partner Referral Error', $response->json());
                return $this->error([], 'PayPal onboarding failed', 400);
            }

            $data = $response->json();
            $onboardingUrl = collect($data['links'])->firstWhere('rel', 'action_url')['href'] ?? null;

            if (!$onboardingUrl) {
                return $this->error([], 'Onboarding URL not found', 400);
            }

            return $this->success(['url' => $onboardingUrl], 'Onboarding link generated');
        }
        return $this->error([], 'Vendor already onboarded with PayPal', 400);
    }

    public function onboardSuccess(Request $request)
    {
        $trackingId = $request->query('tracking_id');

        $vendor = $request->query('user_id');

        Log::error('user id', ['tracking_id' => $trackingId, 'user_id' => $vendor]);

        if (!$trackingId) {
            return $this->error([], 'Tracking ID not found', 400);
        }

        $clientId = config('services.paypal.sandbox.client_id');
        $clientSecret = config('services.paypal.sandbox.client_secret');
        $partnerId = config('services.paypal.sandbox.partner_id');

        //Get Access Token
        $accessTokenResponse = Http::withBasicAuth($clientId, $clientSecret)
            ->asForm()
            ->post('https://api-m.sandbox.paypal.com/v1/oauth2/token', [
                'grant_type' => 'client_credentials'
            ]);

        if (!$accessTokenResponse->successful()) {
            return $this->error([], 'Failed to get PayPal access token', 400);
        }

        $accessToken = $accessTokenResponse->json()['access_token'];

        //Fetch merchant details using tracking_id
        $merchantInfoResponse = Http::withToken($accessToken)
            ->get("https://api-m.sandbox.paypal.com/v1/customer/partners/{$partnerId}/merchant-integrations?tracking_id={$trackingId}");

        if (!$merchantInfoResponse->successful()) {
            Log::error('PayPal merchant info error', $merchantInfoResponse->json());
            return $this->error([], 'PayPal merchant info not found', 400);
        }

        $data = $merchantInfoResponse->json();

        log::error('merchant info', ['data' => $data]);

        //Update vendor info
        $merchantId = $data['merchant_id'] ?? null;
        // $capabilities = collect($data['capabilities'] ?? []);

        // $paymentsReceivable = $capabilities->contains('PAYMENTS_RECEIVABLE');
        // $primaryEmailConfirmed = $capabilities->contains('PRIMARY_EMAIL_CONFIRMED');

        // if (app()->environment('sandbox')) {
        //     $paymentsReceivable = true;
        //     $primaryEmailConfirmed = true;
        // }

        // if (!$merchantId || !$paymentsReceivable || !$primaryEmailConfirmed) {
        //     return $this->error([], 'Vendor PayPal account is not fully verified. Please complete onboarding.', 400);
        // }

        // Save merchant info
        // Save or update PayPal account
        PaypalAccount::updateOrCreate(
            ['user_id' => $vendor],
            [
                'paypal_merchant_id' => $merchantId,
                'paypal_tracking_id' => $trackingId,
                'paypal_email' => $data['primary_email'] ?? null,
            ]
        );


        // Redirect to frontend success_url
        return redirect()->away(config('services.paypal.sandbox.base_url'));
    }

    public function onboardCancel(Request $request)
    {
        $trackingId = $request->query('tracking_id');

        $vendor = User::whereHas('paypalAccount', function ($query) use ($trackingId) {
            $query->where('paypal_tracking_id', $trackingId);
        })->first();

        if (!$vendor) {
            return $this->error([], 'Vendor not found', 404);
        }

        return redirect()->away(config('services.paypal.sandbox.base_url'));
    }
}
