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
    public function onboard()
    {
        $clientId = config('services.paypal.sandbox.client_id');
        $clientSecret = config('services.paypal.sandbox.client_secret');


        $accessToken = Http::withBasicAuth($clientId, $clientSecret)
            ->asForm()
            ->post('https://api-m.sandbox.paypal.com/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ]);


        // dd($accessToken);

        $trackingId = Str::uuid()->toString(); // Unique tracking ID per user

        // Save trackingId to user (optional)
        $vendor = auth()->user();
        $vendor->paypal_tracking_id = $trackingId;
        $vendor->save();

        $payload = [
            "tracking_id" => $trackingId,
            "partner_config_override" => [
                "return_url" => route('paypal.success', ['tracking_id' => $trackingId]),
                "return_url_description" => "The url to return the merchant after the PayPal onboarding process.",
                "show_add_credit_card" => true
            ],
            "operations" => [[
                "operation" => "API_INTEGRATION",
                "api_integration_preference" => [
                    "rest_api_integration" => [
                        "integration_method" => "PAYPAL",
                        "integration_type" => "THIRD_PARTY",
                        "third_party_details" => [
                            "features" => ["PAYMENT", "REFUND"]
                        ]
                    ]
                ]
            ]],
            "products" => ["EXPRESS_CHECKOUT"],
            "legal_consents" => [[
                "type" => "SHARE_DATA_CONSENT",
                "granted" => true
            ]]
        ];

        // dd($payload);

        $accessToken = $accessToken['access_token'];

        $response = Http::withToken($accessToken)
            ->withHeaders([
                'Content-Type' => 'application/json'
            ])
            ->post('https://api-m.sandbox.paypal.com/v2/customer/partner-referrals', $payload);

        // dd($response);

        return $response;

        if (!$response->successful()) {
            Log::error('PayPal Partner Referral Error', $response->json());
            return $this->error([], 'PayPal onboarding failed', 400);
        }

        $onboardingUrl = collect($response->json('links'))->firstWhere('rel', 'action_url')['href'] ?? null;

        return redirect($onboardingUrl);
    }

    public function onboardSuccess(Request $request)
    {
        $trackingId = $request->get('tracking_id');

        if (!$trackingId) {
            return $this->error([], 'Tracking ID not found', 400);
        }

        $clientId = config('services.paypal.sandbox.client_id');
        $clientSecret = config('services.paypal.sandbox.client_secret');

        // Get access token again
        $accessToken = Http::withBasicAuth($clientId, $clientSecret)
            ->asForm()
            ->post('https://api-m.sandbox.paypal.com/v1/oauth2/token', [
                'grant_type' => 'client_credentials'
            ])->json()['access_token'];

        // Fetch merchant info using the tracking ID
        $response = Http::withToken($accessToken)
            ->get("https://api-m.sandbox.paypal.com/v2/customer/partners/{$clientId}/merchant-integrations", [
                'tracking_id' => $trackingId
            ]);

        if (!$response->successful()) {
            return $this->error([], 'PayPal merchant info not found', 400);
        }

        $merchantId = $response['merchant_id'] ?? null;
        $paymentsReceivable = $response['payments_receivable'] ?? false;
        $email = $response['email'] ?? null;

        if (!$merchantId || !$paymentsReceivable) {
            return $this->error([], 'PayPal merchant info not found', 400);
        }

        // Save merchant PayPal info
        $vendor = auth()->user();
        $vendor->paypal_merchant_id = $merchantId;
        $vendor->paypal_email = $email;
        $vendor->save();

        return $this->success($vendor, 'PayPal merchant info fetched successfully', 200);
    }
}
