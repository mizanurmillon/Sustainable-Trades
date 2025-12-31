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

    public function createSubscription(Request $request)
    {
        $planID = $request->input('plan_id');

        $plan = SubscriptionPlan::find($planID);

        if (!$plan) {
            return response()->json(['error' => 'Subscription plan not found'], 404);
        }

        $this->paypal->setApiCredentials(config('paypal'));
        $this->paypal->getAccessToken();

        // Create the subscription in PayPal
        $subscription = $this->paypal->createSubscription([
            "plan_id" => $plan->paypal_plan_id
        ]);

        $data = [
            'orderID' => $subscription['id'],
            'amount'  => $plan->price,
            'plan_id' => $plan->id,
            'status' => $subscription['status'],
            'links' => $subscription['links']
        ];

        return $this->success($data, 'Order created successfully', 200);
    }

    // Capture order
    public function confirmSubscription(Request $request)
    {
        $subscriptionID = $request->input('orderID'); // From frontend after approval
        $planID         = $request->input('plan_id');
        $user           = auth()->user();
        $plan           = SubscriptionPlan::find($planID);

        if (!$plan || !$user) {
            return $this->error(['message' => 'Invalid plan or user'], 400);
        }

        try {
            $this->paypal->setApiCredentials(config('paypal'));
            $this->paypal->getAccessToken();

            // Check subscription status
            $details = $this->paypal->showSubscriptionDetails($subscriptionID);
            // dd($details);

            $status = $details['status'] ?? null;

            if ($status === 'ACTIVE' || $status === 'APPROVED') {

                // 1. Update User
                $user->update(['is_premium' => true]);

                // 2. Calculate End Date (PayPal handles the cycle, but you track it)
                $endDate = now()->addDays($plan->interval_days ?? 30);

                // 3. Save Membership
                $membership = Membership::updateOrCreate(
                    ['user_id' => $user->id], // Assuming one active membership
                    [
                        'order_id' => $subscriptionID, // Store this for canceling later
                        'plan_id'  => $plan->id,
                        'price'    => $plan->price,
                        'membership_type' => $plan->membership_type,
                        'type'     => $plan->interval,
                        'status'   => 'active',
                        'start_at' => now(),
                        'end_at'   => $endDate,
                    ]
                );

                // 4. History
                MembershipHistory::create([
                    'order_id' => $subscriptionID,
                    'membership_id'  => $membership->id,
                    'user_id'         => $user->id,
                    'plan_id'         => $plan->id,
                    'price'           => $plan->price,
                    'membership_type' => $plan->membership_type,
                    'type'            => $plan->interval,
                    'start_at'        => now(),
                    'end_at'          => $endDate,
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'Subscription active and auto-renew enabled',
                    'data' => $membership
                ]);
            }

            return $this->error(['message' => 'Subscription not active'], 422);
        } catch (\Exception $e) {
            return $this->error(['message' => $e->getMessage()], 500);
        }
    }


    public function cancelMembership(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return $this->error(['status' => false, 'message' => 'User not found'], 404);
        }

        $membership = Membership::where('user_id', $user->id)->latest()->first();

        if (!$membership) {
            return $this->error(['status' => false, 'message' => 'Membership not found'], 404);
        }

        // Here you can add logic to cancel the membership in PayPal if needed
        // For example, you might call a PayPal API endpoint to cancel the subscription

        // Update user and membership status
        $user->update(['is_premium' => false]);
        $membership->update(['end_at' => now()]);

        return $this->success(['status' => true, 'message' => 'Membership cancelled successfully'], 200);
    }
}
