<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;



class OnboardingController extends Controller
{
    use ApiResponse;
    public function onboard()
    {
        $clientId = config('services.paypal.client_id');
        $clientSecret = config('services.paypal.client_secret');

        $accessToken = Http::withBasicAuth($clientId, $clientSecret)
            ->asForm()
            ->post('https://api-m.sandbox.paypal.com/v1/oauth2/token', [
                'grant_type' => 'client_credentials'
            ])->json()['access_token'];

        $payload = [
            "tracking_id" => Str::uuid(),
            "partner_config_override" => [
                "return_url" => route('paypal.success'),
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

        $response = Http::withToken($accessToken)
            ->post('https://api-m.sandbox.paypal.com/v2/customer/partner-referrals', $payload);

        if (!$response->successful()) {
            // Log the error or return it for debugging
            return $this->error([
                'status' => $response->status(),
                'body' => $response->body(),
            ], 'PayPal onboarding API failed', 500);
        }

        $links = $response['links'] ?? null;

        if (!$links) {
            return $this->error([], 'PayPal response does not contain action_url', 500);
        }

        $onboardingUrl = collect($links)->firstWhere('rel', 'action_url')['href'] ?? null;

        if (!$onboardingUrl) {
            return $this->error([], 'Onboarding URL not found in PayPal response', 500);
        }

        return redirect($onboardingUrl);
    }

    public function onboardSuccess(Request $request)
    {
        $merchantId = $request->get('merchantIdInPayPal'); // Save this in DB

        // Optional: Save to DB (auth()->user() or vendor logic)
        $user = auth()->user();
        $user->paypal_merchant_id = $merchantId;
        $user->save();

        return $this->success($user, 'User authenticated successfully', 200);;
    }
}
