<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class OnboardingController extends Controller
{
    use ApiResponse;

    public function onboard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'success_url' => 'required|url',
            'cancel_url' => 'required|url',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation error', 400);
        }

        $clientId = config('services.paypal.sandbox.client_id');
        $clientSecret = config('services.paypal.sandbox.client_secret');

        // 1️⃣ Get access token
        $tokenResponse = Http::withBasicAuth($clientId, $clientSecret)
            ->asForm()
            ->post('https://api-m.sandbox.paypal.com/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ]);

        if (!$tokenResponse->successful()) {
            return $this->error([], 'Failed to get PayPal access token', 400);
        }

        $accessToken = $tokenResponse->json()['access_token'];

        // 2️⃣ Generate tracking ID
        $trackingId = (string) Str::uuid();

        /** @var User $vendor */
        $vendor = auth()->user();
        $vendor->paypal_tracking_id = $trackingId;
        $vendor->save();

        // 3️⃣ Create Partner Referral
        $payload = [
            "tracking_id" => $trackingId,
            "partner_config_override" => [
                "return_url" => route('paypal.onboard.success', ['tracking_id' => $trackingId]),
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

    public function onboardSuccess(Request $request)
    {
        $trackingId = $request->query('tracking_id');

        if (!$trackingId) {
            return $this->error([], 'Tracking ID not found', 400);
        }

        $vendor = User::where('paypal_tracking_id', $trackingId)->first();
        if (!$vendor) {
            return $this->error([], 'Vendor not found', 404);
        }

        $clientId = config('services.paypal.sandbox.client_id');
        $clientSecret = config('services.paypal.sandbox.client_secret');

        // 1️⃣ Get Access Token
        $accessTokenResponse = Http::withBasicAuth($clientId, $clientSecret)
            ->asForm()
            ->post('https://api-m.sandbox.paypal.com/v1/oauth2/token', [
                'grant_type' => 'client_credentials'
            ]);

        if (!$accessTokenResponse->successful()) {
            return $this->error([], 'Failed to get PayPal access token', 400);
        }

        $accessToken = $accessTokenResponse->json()['access_token'];

        // 2️⃣ Fetch merchant details using tracking_id
        $merchantInfoResponse = Http::withToken($accessToken)
            ->get("https://api-m.sandbox.paypal.com/v1/customer/partners/{$clientId}/merchant-integrations?tracking_id={$trackingId}");

        if (!$merchantInfoResponse->successful()) {
            Log::error('PayPal merchant info error', $merchantInfoResponse->json());
            return $this->error([], 'PayPal merchant info not found', 400);
        }

        $data = $merchantInfoResponse->json();

        // 3️⃣ Update vendor info
        $vendor->paypal_merchant_id = $data['merchant_id'] ?? null;
        $vendor->paypal_email = $data['email'] ?? null;
        $vendor->paypal_tracking_id = $data['tracking_id'] ?? null;
        $vendor->save();

        // 4️⃣ Redirect to frontend success_url
        return redirect()->away($request->success_url);
    }

    public function onboardCancel(Request $request)
    {
        $trackingId = $request->query('tracking_id');
        $vendor = User::where('paypal_tracking_id', $trackingId)->first();

        if (!$vendor) {
            return $this->error([], 'Vendor not found', 404);
        }

        return redirect()->away($request->cancel_url);
    }
}
