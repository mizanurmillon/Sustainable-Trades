<?php

namespace App\Service;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PayPalSubscriptionService
{
    protected $clientId;
    protected $secret;
    protected $baseUrl;

    public function __construct()
    {
        $this->clientId = config('services.paypal.sandbox.client_id');
        $this->secret = config('services.paypal.sandbox.client_secret');
        $this->baseUrl = config('services.paypal.mode') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    public function getAccessToken()
    {
        $response = Http::asForm()->withBasicAuth($this->clientId, $this->secret)
            ->post("{$this->baseUrl}/v1/oauth2/token", [
                'grant_type' => 'client_credentials',
            ]);

        $json = $response->json();

        if (!isset($json['access_token'])) {
            logger()->error('PayPal Access Token Error', [
                'status' => $response->status(),
                'body' => $json,
            ]);

            throw new \Exception('Failed to get access token from PayPal. Response: ' . json_encode($json));
        }

        return $json['access_token'];
    }

    public function createProduct($name, $description)
    {
        $token = $this->getAccessToken();

        $response = Http::withToken($token)->post("{$this->baseUrl}/v1/catalogs/products", [
            'name' => $name,
            'description' => $description,
            'type' => 'SERVICE',
            'category' => 'SOFTWARE',
        ]);

        if (!$response->successful()) {
            throw new \Exception("PayPal Product Creation Failed: " . $response->body());
        }

        return $response->json();
    }

    public function createPlan($productId, $name, $description, $price, $interval)
    {
        $token = $this->getAccessToken();

        $response = Http::withToken($token)->post("{$this->baseUrl}/v1/billing/plans", [
            'product_id' => $productId,
            'name' => $name,
            'description' => $description,
            'billing_cycles' => [[
                'frequency' => [
                    'interval_unit' => strtoupper($interval), // make sure it's 'MONTH' or 'YEAR'
                    'interval_count' => 1
                ],
                'tenure_type' => 'REGULAR',
                'sequence' => 1,
                'total_cycles' => 0,
                'pricing_scheme' => [
                    'fixed_price' => [
                        'value' => number_format($price, 2, '.', ''),
                        'currency_code' => 'USD'
                    ]
                ]
            ]],
            'payment_preferences' => [
                'auto_bill_outstanding' => true,
                'setup_fee' => [
                    'value' => '0',
                    'currency_code' => 'USD'
                ],
                'setup_fee_failure_action' => 'CONTINUE',
                'payment_failure_threshold' => 3
            ]
        ]);

        if (!$response->successful()) {
            throw new \Exception("PayPal Plan Creation Failed: " . $response->body());
        }

        return $response->json();
    }


    public function createSubscription($planId)
    {
        $token = $this->getAccessToken();

        $response = Http::withToken($token)->post("{$this->baseUrl}/v1/billing/subscriptions", [
            'plan_id' => $planId,
            'application_context' => [
                'brand_name' => 'Sustainable Trades',
                'locale' => 'en-US',
                'user_action' => 'SUBSCRIBE_NOW',
                // 'return_url' => route('paypal.subscription.success'),
                // 'cancel_url' => route('paypal.subscription.cancel'),
            ]
        ]);

        return $response->json();
    }

    public function updateProduct($productId, $data)
    {
        $url = "{$this->baseUrl}/v1/catalogs/products/{$productId}";

        $body = [];
        foreach ($data as $key => $value) {
            $body[] = [
                'op' => 'replace',
                'path' => "/{$key}",
                'value' => $value
            ];
        }

        try {
            return $this->makeRequest('PATCH', $url, $body);
        } catch (\Exception $e) {
            Log::error('PayPal Product Update Failed', [
                'product_id' => $productId,
                'payload' => $body,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function makeRequest($method, $url, $body)
    {
        $token = $this->getAccessToken(); // Token যোগ করো

        $response = Http::withToken($token)->send($method, $url, [
            'json' => $body,
        ]);

        if (!$response->successful()) {
            throw new \Exception("PayPal API Request Failed: " . $response->body());
        }

        return $response->json(); // Proper JSON return
    }
}
