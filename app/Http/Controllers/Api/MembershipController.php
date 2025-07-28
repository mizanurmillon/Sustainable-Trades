<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\SubscriptionPlan;
use App\Traits\ApiResponse; // Assuming this is your trait for API responses
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class MembershipController extends Controller
{
    use ApiResponse;

    protected $paypal;

    public function __construct()
    {
       $this->paypal = new PayPalClient;
        $this->paypal->setApiCredentials(config('services.paypal'));
    }

    public function Membership(Request $request, $id)
    {
        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'User not found', 200);
        }

        $paypalPlan = SubscriptionPlan::where('id', $id)->first();

        if (!$paypalPlan) {
            return $this->error([], 'Subscription plan not found', 404);
        }

        $data = [
            'plan_id' => $paypalPlan->paypal_plan_id,
            'quantity' => '1',
            'shipping_amount' => [
                'currency_code' => 'USD',
                'value' => 0.00,
            ],
            'subscriber' => [
                'name' => [
                    'given_name' => $user->first_name,
                    'surname' => $user->last_name,
                ],
                'email_address' => $user->email,
                'shipping_address' => [
                    'name' => [
                        'full_name' => $user->id . '-' . $user->first_name . ' ' . $user->last_name,
                    ],
                    'address' => $user->address ? [
                        'address_line_1' => $user->address ?? '',
                        'admin_area_2' => $user->city ?? '',
                        'admin_area_1' => $user->state ?? '',
                        'postal_code' => $user->zip ?? '',
                        'country_code' => $user->country ?? 'US',
                    ] : [],
                ],
            ],
            'application_context' => [
                'brand_name' => config('app.name'),
                'locale' => 'en-US',
                'shipping_preference' => 'SET_PROVIDED_ADDRESS',
                'user_action' => 'SUBSCRIBE_NOW',
                'payment_method' => [
                    'payer_selected' => 'PAYPAL',
                    'payee_preferred' => 'IMMEDIATE_PAYMENT_REQUIRED',
                ],
                'return_url' => route('payments.paypal.success'),
                'cancel_url' => route('payments.cancel'),
            ],
        ];

        // Include additional data for subscription with a coupon
        if ($user->id != 0) {
            $data['plan'] = [
                'billing_cycles' => [
                    [
                        'sequence' => 1,
                        'total_cycles' => 1,
                        'pricing_scheme' => [
                            'fixed_price' => [
                                'value' => $paypalPlan->price,
                                'currency_code' => 'USD',
                            ],
                        ],
                    ],
                ],
            ];
        }

        try {
            $response = $this->provider->createSubscription($data);
            Log::info('PayPal Subscription Response: ', $response); // Debug response

            if (isset($response['id']) && $response['id'] != null) {
                foreach ($response['links'] as $link) {
                    if ($link['rel'] == 'approve') {
                        return redirect()->away($link['href']);
                    }
                }
                return $this->error([], 'No approval link found', 200);
            }

            return $this->error([], $response['message'] ?? 'An error occurred while creating the subscription', 200);
        } catch (\Exception $e) {
            Log::error('PayPal Subscription Error: ', ['error' => $e->getMessage()]);
            return $this->error([], 'Failed to create subscription: ' . $e->getMessage(), 500);
        }
    }

    public function success(Request $request)
    {
        $subscription_id = $request->get('subscription_id');

        if (!$subscription_id) {
            return $this->error([], 'Subscription ID not provided', 400);
        }

        try {
            $details = $this->provider->showSubscriptionDetails($subscription_id);
            Log::info('PayPal Subscription Details: ', $details); // Debug response

            $plan = SubscriptionPlan::where('paypal_plan_id', $details['plan_id'])->first();

            if (!$plan) {
                return $this->error([], 'Plan not found', 404);
            }

            $data = Membership::create([
                'order_id' => rand(100000, 999999),
                'user_id' => auth()->user()->id,
                'plan_id' => $plan->id,
                'membership_type' => $plan->membership_type ?? 'default',
                'type' => $details['status'] ?? 'active',
                'price' => $plan->price,
                'start_at' => Carbon::now(),
                'end_at' => Carbon::parse($details['billing_info']['next_billing_time'] ?? now()->addMonth())->toDateTimeString(),
            ]);

            return $this->success($data, 'Subscription created successfully', 200);
        } catch (\Exception $e) {
            Log::error('PayPal Success Error: ', ['error' => $e->getMessage()]);
            return $this->error([], 'Failed to process subscription: ' . $e->getMessage(), 500);
        }
    }
}
