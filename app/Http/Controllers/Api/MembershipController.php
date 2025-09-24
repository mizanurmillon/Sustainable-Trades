<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\MembershipHistory;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Traits\ApiResponse; // Assuming this is your trait for API responses
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class MembershipController extends Controller
{
    use ApiResponse;

    protected $provider;

    public function __construct()
    {
        $this->provider = new PayPalClient;
        $this->provider->setApiCredentials(config('paypal'));
    }

    public function createMembership(Request $request, $id)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'success_url' => 'required|url',
            'cancel_url' => 'required|url',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), $validator->errors()->first(), 422);
        }

        $user = auth()->user();
        if (!$user) return $this->error([], 'User not found', 404);

        $paypalPlan = SubscriptionPlan::find($id);
        if (!$paypalPlan) return $this->error([], 'Subscription plan not found', 404);

        $data = [
            'plan_id' => $paypalPlan->paypal_plan_id,
            'subscriber' => [
                'name' => [
                    'given_name' => $user->first_name ?? '',
                    'surname'   => $user->last_name ?? '',
                ],
                'email_address' => $user->email,
            ],
            'application_context' => [
                'brand_name'          => config('app.name'),
                'locale'              => 'en-US',
                'shipping_preference' => 'NO_SHIPPING',
                'user_action'         => 'SUBSCRIBE_NOW',
                'payment_method' => [
                    'payer_selected'  => 'PAYPAL',
                    'payee_preferred' => 'IMMEDIATE_PAYMENT_REQUIRED',
                ],
                'return_url' => route('payments.paypal.success', [
                    'user_id' => $user->id,
                    'success_url' => $request->get('success_url') // pass it here
                ]),
                'cancel_url' => route('payments.cancel') . '?cancel_url=' . urlencode($request->get('cancel_url')),
            ],
        ];


        try {
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $token = $provider->getAccessToken();
            $provider->setAccessToken($token);

            $response = $provider->createSubscription($data);

            // Log::info('PayPal Subscription Creation Response: ' . json_encode($response));


            if (isset($response['id'])) {
                foreach ($response['links'] as $link) {
                    if ($link['rel'] == 'approve') {
                        return response()->json([
                            'success' => true,
                            'message' => 'Subscription created successfully',
                            'data'    => ['url' => $link['href']],
                        ], 200);
                    }
                }
                return $this->error([], 'Approval link not found', 500);
            }
            return $this->error($response, 'Subscription creation failed', 500);
        } catch (\Exception $e) {
            Log::error('PayPal Subscription Creation Error: ' . $e->getMessage());
            return $this->error([], 'Exception: ' . $e->getMessage(), 500);
        }
    }

    public function success(Request $request)
    {
        $subscription_id = $request->get('subscription_id');
        $userId = $request->get('user_id');
        $successUrl = $request->get('success_url');

        if (!$subscription_id) {
            return $this->error([], 'Subscription ID not provided', 400);
        }

        try {
            $provider = new PayPalClient();
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();

            $details = $provider->showSubscriptionDetails($subscription_id);

            $plan = SubscriptionPlan::where('paypal_plan_id', $details['plan_id'])->first();
            if (!$plan) {
                return $this->error([], 'Plan not found', 404);
            }

            $user = User::find($userId);
            if (!$user) {
                return $this->error([], 'User not found', 404);
            }

            // User premium update
            $user->update([
                'is_premium' => true,
            ]);

            // Billing info
            $nextBilling = $details['billing_info']['next_billing_time'] ?? null;
            $endDate = $nextBilling
                ? Carbon::parse($nextBilling)->toDateTimeString()
                : Carbon::now()->addMonth()->toDateTimeString();

            // Save Membership
            $membership = Membership::create([
                'order_id'        => 'ORD-' . strtoupper(Str::random(10)),
                'user_id'         => $user->id,
                'plan_id'         => $plan->id,
                'membership_type' => $plan->membership_type ?? '',
                'type'            => $plan->interval ?? '',
                'price'           => $plan->price,
                'start_at'        => Carbon::now(),
                'end_at'          => $endDate,
            ]);

            // Save History
            MembershipHistory::create([
                'order_id'        => $membership->order_id,
                'user_id'         => $membership->user_id,
                'membership_id'   => $membership->id,
                'plan_id'         => $plan->id,
                'membership_type' => $plan->membership_type,
                'type'            => $plan->interval,
                'price'           => $plan->price,
                'start_at'        => Carbon::now(),
                'end_at'          => $endDate,
            ]);

            return redirect($successUrl);
        } catch (\Exception $e) {
            Log::error('PayPal Success Error: ' . $e->getMessage());
            return $this->error([], 'Failed to process subscription: ' . $e->getMessage(), 500);
        }
    }

    public function paypalCancel(Request $request)
    {

        return redirect($request->get('cancel_url'));
    }
}
