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

    protected $paypal;

    public function __construct()
    {
        $this->paypal = new PayPalClient;
        $this->paypal->setApiCredentials(config('paypal'));
        $this->paypal->getAccessToken();
    }

    public function createOrder(Request $request)
    {
        $planID = $request->input('plan_id');

        $plan = SubscriptionPlan::find($planID);

        if (!$plan) {
            return response()->json(['error' => 'Subscription plan not found'], 404);
        }

        $order = $this->paypal->createOrder([
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => $plan->price,
                    ],
                    "description" => $plan->name,
                ],
            ]
        ]);

        $data = [
            'orderID' => $order['id'],
            'amount'  => $plan->price,
            'plan_id' => $plan->id,
            'status' => $order['status'],
        ];

        return $this->success($data, 'Order created successfully', 200);
    }

    // Capture order
    public function captureOrder(Request $request)
    {
        $orderID = $request->input('orderID');
        $planID  = $request->input('plan_id');

        $user = $request->user();
        $plan = SubscriptionPlan::find($planID);

        if (!$plan || !$user) {
            return response()->json(['status' => false, 'message' => 'Invalid plan or user'], 400);
        }

        try {
            $this->paypal->setApiCredentials(config('paypal'));
            $this->paypal->getAccessToken();
            
            // Step B: Capture payment
            $capture = $this->paypal->capturePaymentOrder($orderID);

            if (($capture['status'] ?? null) === 'COMPLETED') {

                $user->update(['is_premium' => true]);

                $endDate = now()->addDays($plan->interval_days ?? 30);

                $membership = Membership::create([
                    'order_id' => $orderID,
                    'user_id'  => $user->id,
                    'plan_id'  => $plan->id,
                    'price'    => $plan->price,
                    'start_at' => now(),
                    'end_at'   => $endDate,
                ]);

                MembershipHistory::create([
                    'order_id'      => $orderID,
                    'user_id'       => $user->id,
                    'membership_id' => $membership->id,
                    'plan_id'       => $plan->id,
                    'price'         => $plan->price,
                    'start_at'      => now(),
                    'end_at'        => $endDate,
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Payment captured successfully',
                    'data' => $membership
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'Payment not completed',
                'paypal_status' => $capture['status'] ?? null,
                'raw' => $capture
            ], 422);
        } catch (\Exception $e) {
            Log::error('PayPal capture error', ['message' => $e->getMessage()]);
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }








    // public function createMembership(Request $request, $id)
    // {

    //     $validator = Validator::make($request->all(), [
    //         'success_url' => 'required|url',
    //         'cancel_url' => 'required|url',
    //     ]);

    //     if ($validator->fails()) {
    //         return $this->error($validator->errors(), 'Validation error', 400);
    //     }

    //     $user = auth()->user();

    //     if (!$user) return $this->error([], 'User not found', 200);

    //     $paypalPlan = SubscriptionPlan::find($id);

    //     if (!$paypalPlan) return $this->error([], 'Subscription plan not found', 404);

    //     $data = [
    //         'plan_id' => $paypalPlan->paypal_plan_id,
    //         'subscriber' => [
    //             'name' => [
    //                 'given_name' => $user->first_name,
    //                 'surname' => $user->last_name,
    //             ],
    //             'email_address' => $user->email,
    //         ],
    //         'application_context' => [
    //             'brand_name' => config('app.name'),
    //             'locale' => 'en-US',
    //             'shipping_preference' => 'NO_SHIPPING',
    //             'user_action' => 'SUBSCRIBE_NOW',
    //             'return_url' => route('payments.paypal.success') . '?user_id=' . $user->id . '&success_url=' . urlencode($request->success_url),
    //             'cancel_url' => route('payments.cancel') . '?cancel_url=' . urlencode($request->cancel_url),
    //         ],
    //     ];

    //     try {
    //         $provider = new PayPalClient;
    //         $provider->setApiCredentials(config('paypal'));  // Make sure config has correct credentials
    //         $token = $provider->getAccessToken();
    //         $provider->setAccessToken($token);

    //         $response = $provider->createSubscription($data);

    //         return response()->json([
    //             'success' => true,
    //             'data' => [
    //                 'subscription_id' => $response['id'],
    //                 // 'link' => collect($response['links'])->firstWhere('rel', 'approve')['href'],
    //             ]
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error('PayPal Subscription Creation Error: ' . $e->getMessage());
    //         return $this->error([], 'Exception: ' . $e->getMessage(), 500);
    //     }
    // }

    // public function success(Request $request)
    // {
    //     // Log incoming request to debug
    //     Log::info('PayPal success callback data', $request->all());

    //     $subscription_id = $request->get('subscription_id');
    //     $userId = $request->get('user_id');

    //     $success_url = $request->get('success_url');

    //     if (!$subscription_id) {
    //         return $this->error([], 'Subscription ID not provided', 400);
    //     }

    //     if (!$userId) {
    //         return $this->error([], 'User ID not provided', 400);
    //     }

    //     try {
    //         // Initialize PayPal client
    //         $provider = new PayPalClient();
    //         $provider->setApiCredentials(config('paypal'));
    //         $token = $provider->getAccessToken();
    //         $provider->setAccessToken($token);

    //         // Fetch subscription details
    //         $details = $provider->showSubscriptionDetails($subscription_id);
    //         Log::info('PayPal subscription details', $details);

    //         if (!isset($details['plan_id'])) {
    //             return $this->error([], 'Plan ID not found in subscription details', 500);
    //         }

    //         $plan = SubscriptionPlan::where('paypal_plan_id', $details['plan_id'])->first();
    //         if (!$plan) {
    //             return $this->error([], 'Plan not found', 404);
    //         }

    //         $user = User::find($userId);
    //         if (!$user) {
    //             return $this->error([], 'User not found', 404);
    //         }

    //         // Determine membership end date
    //         $nextBilling = $details['billing_info']['next_billing_time'] ?? null;
    //         $endDate = $nextBilling
    //             ? Carbon::parse($nextBilling)->toDateTimeString()
    //             : Carbon::now()->addMonth()->toDateTimeString();

    //         DB::beginTransaction();

    //         // Update user to premium
    //         $user->update(['is_premium' => true]);

    //         // Create membership
    //         $membership = Membership::create([
    //             'order_id'        => 'ORD-' . strtoupper(Str::random(10)),
    //             'user_id'         => $user->id,
    //             'plan_id'         => $plan->id,
    //             'membership_type' => $plan->membership_type ?? '',
    //             'type'            => $plan->interval ?? '',
    //             'price'           => $plan->price,
    //             'start_at'        => Carbon::now(),
    //             'end_at'          => $endDate,
    //         ]);

    //         // Create membership history
    //         MembershipHistory::create([
    //             'order_id'        => $membership->order_id,
    //             'user_id'         => $user->id,
    //             'membership_id'   => $membership->id,
    //             'plan_id'         => $plan->id,
    //             'membership_type' => $plan->membership_type ?? '',
    //             'type'            => $plan->interval ?? '',
    //             'price'           => $plan->price,
    //             'start_at'        => Carbon::now(),
    //             'end_at'          => $endDate,
    //         ]);

    //         DB::commit();
    //         // Redirect to success URL if provided

    //         if ($success_url) {
    //             return redirect()->away($success_url);
    //         }
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return $this->error([], 'Failed to process subscription: ' . $e->getMessage(), 500);
    //     }
    // }

    // public function paypalCancel(Request $request)
    // {
    //     return $this->error([], 'User cancelled the subscription', 200);
    //     // Handle the cancellation logic here
    //     // Redirect to the cancel URL
    //     if ($cancel_url) {
    //         return redirect()->away($cancel_url);
    //     }
    // }
}
