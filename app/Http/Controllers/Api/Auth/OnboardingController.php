<?php

namespace App\Http\Controllers\Api\Auth;

use App\Traits\ApiResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class OnboardingController extends Controller
{
    use ApiResponse;

    /**
     * Generate PayPal Partner Referral (onboarding link)
     */
    public function onboard(Request $request)
    {
        $clientId = config('services.paypal.sandbox.client_id');
        $clientSecret = config('services.paypal.sandbox.client_secret');

        // 🔹 Step 1: Get Access Token
        $tokenResponse = Http::withBasicAuth($clientId, $clientSecret)
            ->asForm()
            ->post('https://api-m.sandbox.paypal.com/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ]);

        // dd($tokenResponse->json());

        if (!$tokenResponse->successful()) {
            return $this->error([], 'Failed to get PayPal access token', 400);
        }

        $accessToken = $tokenResponse->json()['access_token'];

        // 🔹 Step 2: Generate Tracking ID
        $trackingId = (string) Str::uuid();

        // Save tracking ID to user
        $vendor = auth()->user();

        $vendor->paypal_tracking_id = $trackingId;
        $vendor->save();


        // 🔹 Step 3: Prepare Payload
        $payload = [
            "tracking_id" => $trackingId,
            "partner_config_override" => [
                'return_url' => route('account.success', [
                    'user_id' => $vendor->id,
                    'success_url' => $request->get('success_url')
                ]),
                'cancel_url' => route('account.cancel') . '?cancel_url=' . urlencode($request->get('cancel_url')),
                "return_url_description" => "Redirect after onboarding",
                "show_add_credit_card" => true,
            ],
            "operations" => [[
                "operation" => "API_INTEGRATION",
                "api_integration_preference" => [
                    "rest_api_integration" => [
                        "integration_method" => "PAYPAL",
                        "integration_type" => "THIRD_PARTY",
                        "third_party_details" => [
                            "features" => ["PAYMENT", "REFUND"],
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

        // 🔹 Step 4: Create Partner Referral
        $response = Http::withToken($accessToken)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post('https://api-m.sandbox.paypal.com/v2/customer/partner-referrals', $payload);
        
        // dd($response->json());

        if (!$response->successful()) {
            Log::error('PayPal Partner Referral Error', $response->json());
            return $this->error([], 'PayPal onboarding failed', 400);
        }

        $data = $response->json();
        $onboardingUrl = collect($data['links'])->firstWhere('rel', 'action_url')['href'] ?? null;

        if (!$onboardingUrl) {
            return $this->error([], 'Onboarding URL not found', 400);
        }

        // 🔹 Redirect to PayPal onboarding URL
        return $this->success(['url' => $onboardingUrl], 'Onboarding link generated');
    }

    /**
     * Handle success redirect from PayPal
     */
    public function onboardSuccess(Request $request)
    {
        $trackingId = $request->get('tracking_id');

        if (!$trackingId) {
            return $this->error([], 'Tracking ID not found', 400);
        }

        $clientId = config('services.paypal.sandbox.client_id');
        $clientSecret = config('services.paypal.sandbox.client_secret');

        // 🔹 Get Access Token again
        $accessTokenResponse = Http::withBasicAuth($clientId, $clientSecret)
            ->asForm()
            ->post('https://api-m.sandbox.paypal.com/v1/oauth2/token', [
                'grant_type' => 'client_credentials'
            ]);

        if (!$accessTokenResponse->successful()) {
            return $this->error([], 'Failed to get PayPal access token', 400);
        }

        $accessToken = $accessTokenResponse->json()['access_token'];

        // 🔹 Fetch merchant integration info
        $response = Http::withToken($accessToken)
            ->get("https://api-m.sandbox.paypal.com/v1/customer/partners/{$clientId}/merchant-integrations", [
                'tracking_id' => $trackingId
            ]);

        if (!$response->successful()) {
            return $this->error([], 'PayPal merchant info not found', 400);
        }

        $data = $response->json();

        $merchantId = $data['merchant_id'] ?? null;
        $paymentsReceivable = $data['payments_receivable'] ?? false;
        $email = $data['email'] ?? null;

        if (!$merchantId) {
            return $this->error([], 'Merchant ID not found', 400);
        }

        // 🔹 Save merchant PayPal info
        $vendor = auth()->user();
        $vendor->paypal_merchant_id = $merchantId;
        $vendor->paypal_email = $email;
        $vendor->paypal_payments_receivable = $paymentsReceivable;
        $vendor->save();

        return $this->success($vendor, 'PayPal merchant info fetched successfully');
    }
}
