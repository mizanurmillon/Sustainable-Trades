<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Membership;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;
use App\Models\MembershipHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
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

    public function createMembership(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'success_url' => 'required|url',
            'cancel_url' => 'required|url',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation error', 400);
        }

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
                'return_url' => route('payments.paypal.success') . '?user_id=' . $user->id . '&success_url=' . urlencode($request->success_url),
                'cancel_url' => route('payments.cancel') . '?cancel_url=' . urlencode($request->cancel_url),
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
        // Log incoming request to debug
        Log::info('PayPal success callback data', $request->all());

        $subscription_id = $request->get('subscription_id');
        $userId = $request->get('user_id');

        $success_url = $request->get('success_url');

        if (!$subscription_id) {
            return $this->error([], 'Subscription ID not provided', 400);
        }

        if (!$userId) {
            return $this->error([], 'User ID not provided', 400);
        }

        try {
            // Initialize PayPal client
            $provider = new PayPalClient();
            $provider->setApiCredentials(config('paypal'));
            $token = $provider->getAccessToken();
            $provider->setAccessToken($token);

            // Fetch subscription details
            $details = $provider->showSubscriptionDetails($subscription_id);
            Log::info('PayPal subscription details', $details);

            if (!isset($details['plan_id'])) {
                return $this->error([], 'Plan ID not found in subscription details', 500);
            }

            $plan = SubscriptionPlan::where('paypal_plan_id', $details['plan_id'])->first();
            if (!$plan) {
                return $this->error([], 'Plan not found', 404);
            }

            $user = User::find($userId);
            if (!$user) {
                return $this->error([], 'User not found', 404);
            }

            // Determine membership end date
            $nextBilling = $details['billing_info']['next_billing_time'] ?? null;
            $endDate = $nextBilling
                ? Carbon::parse($nextBilling)->toDateTimeString()
                : Carbon::now()->addMonth()->toDateTimeString();

            DB::beginTransaction();

            // Update user to premium
            $user->update(['is_premium' => true]);

            // Create membership
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

            // Create membership history
            MembershipHistory::create([
                'order_id'        => $membership->order_id,
                'user_id'         => $user->id,
                'membership_id'   => $membership->id,
                'plan_id'         => $plan->id,
                'membership_type' => $plan->membership_type ?? '',
                'type'            => $plan->interval ?? '',
                'price'           => $plan->price,
                'start_at'        => Carbon::now(),
                'end_at'          => $endDate,
            ]);

            DB::commit();
            // Redirect to success URL if provided

            if ($success_url) {
                return redirect()->away($success_url);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error([], 'Failed to process subscription: ' . $e->getMessage(), 500);
        }
    }





    public function paypalCancel(Request $request)
    {
        return $this->error([], 'User cancelled the subscription', 200);
        // Handle the cancellation logic here

         $cancel_url = $request->get('cancel_url');

        // Redirect to the cancel URL
        if ($cancel_url) {
            return redirect()->away($cancel_url);
        }
    }
}
