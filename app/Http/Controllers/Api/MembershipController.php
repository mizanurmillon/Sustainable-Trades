<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Membership;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\MembershipHistory;
use App\Models\User;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Traits\ApiResponse; // Assuming this is your trait for API responses

class MembershipController extends Controller
{
    use ApiResponse;

    protected $provider;

    public function __construct()
    {
        $this->provider = new PayPalClient;
        $this->provider->setApiCredentials(config('paypal'));
    }

    public function Membership(Request $request, $id)
    {
        $user = auth()->user();

        if (!$user) return $this->error([], 'User not found', 200);

        $paypalPlan = SubscriptionPlan::find($id);

        if (!$paypalPlan) return $this->error([], 'Subscription plan not found', 404);

        $data = [
            'plan_id' => $paypalPlan->paypal_plan_id,
            'subscriber' => [
                'name' => [
                    'given_name' => $user->first_name,
                    'surname' => $user->last_name,
                ],
                'email_address' => $user->email,
            ],
            'application_context' => [
                'brand_name' => config('app.name'),
                'locale' => 'en-US',
                'shipping_preference' => 'NO_SHIPPING',
                'user_action' => 'SUBSCRIBE_NOW',
                'payment_method' => [
                    'payer_selected' => 'PAYPAL',
                    'payee_preferred' => 'IMMEDIATE_PAYMENT_REQUIRED',
                ],
                'return_url' => route('payments.paypal.success',['user_id' => $user->id]),
                'cancel_url' => route('payments.cancel'),
            ],
        ];

        try {
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));  // Make sure config has correct credentials
            $token = $provider->getAccessToken();
            $provider->setAccessToken($token);

            $response = $provider->createSubscription($data);

            if (isset($response['id'])) {
                // Find the approval URL and redirect user there
                foreach ($response['links'] as $link) {
                    if ($link['rel'] == 'approve') {
                        // return $link['href'];
                        return response()->json([
                            'success' => true,
                            'message' => 'Subscription created successfully',
                            'data' => ['url' => $link['href']],
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

        if (!$subscription_id) {
            return $this->error([], 'Subscription ID not provided', 400);
        }

        try {
            $this->provider = new PayPalClient();
            $this->provider->setApiCredentials(config('paypal'));
            $this->provider->getAccessToken();

            $details = $this->provider->showSubscriptionDetails($subscription_id);

            $plan = SubscriptionPlan::where('paypal_plan_id', $details['plan_id'])->first();

            if (!$plan) {
                return $this->error([], 'Plan not found', 404);
            }

            User::where('id', $userId)->update([
                'is_premium' => true,
            ]);


            $membership = Membership::create([
                'order_id' => 'ORD-' . strtoupper(Str::random(10)),
                'user_id' => $userId,
                'plan_id' => $plan->id,
                'membership_type' => $plan->membership_type ?? '',
                'type' => $plan->interval ?? '',
                'price' => $plan->price,
                'start_at' => Carbon::now(),
                'end_at' => Carbon::parse($details['billing_info']['next_billing_time'] ?? now()->addMonth())->toDateTimeString(),
            ]);

            if (!$membership) {
                return $this->error([], 'Failed to create membership', 500);
            }

            MembershipHistory::create([
                'order_id' => $membership->order_id,
                'user_id' => $membership->user_id,
                'membership_id'=> $membership->id,
                'plan_id'=> $plan->id,
                'membership_type'=> $plan->membership_type,
                'type'=> $plan->interval,
                'price'=> $plan->price,
                'start_at'=> Carbon::now(),
                'end_at'=> Carbon::parse($details['billing_info']['next_billing_time'] ?? now()->addMonth())->toDateTimeString(),
            ]);

            return redirect('/payment/success');
        } catch (\Exception $e) {
            Log::error('PayPal Success Error: ', ['error' => $e->getMessage()]);
            return $this->error([], 'Failed to process subscription: ' . $e->getMessage(), 500);
        }
    }

    public function paypalCancel()
    {
        return $this->error([], 'User cancelled the subscription', 200);
    }
}
