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

    // public function __construct()
    // {
    //     $this->provider = new PayPalClient;
    //     $this->provider->setApiCredentials(config('paypal'));
    // }

    public function createMembership(Request $request, $id)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'card_holder_name' => 'required|string',
            'card_number'      => 'required|string|max:20',
            'cvv'              => 'required|string|max:4',
            'expiry_date'      => 'required|string|max:7', // MM/YYYY
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), $validator->errors()->first(), 422);
        }

        $user = auth()->user();
        if (!$user) {
            return $this->error([], 'User not found', 404);
        }

        $plan = SubscriptionPlan::find($id);
        if (!$plan) {
            return $this->error([], 'Plan not found', 404);
        }

        try {
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $token = $provider->getAccessToken();
            $provider->setAccessToken($token);

            // Create order (direct card payment)
            $orderData = [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'amount' => [
                            'currency_code' => 'USD',
                            'value' => $plan->price,
                        ],
                        'description' => $plan->membership_type
                    ]
                ],
                'payment_source' => [
                    'card' => [
                        'number' => $request->card_number,
                        'expiry' => $request->expiry_date,
                        'security_code' => $request->cvv,
                        'name' => $request->card_holder_name,
                    ]
                ]
            ];

            $response = $provider->createOrder($orderData);

            if(isset($response['status']) && $response['status'] === 'COMPLETED'){
                // Payment successful, save membership
                $endDate = Carbon::now()->addMonth()->toDateTimeString();

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

                MembershipHistory::create([
                    'order_id'        => $membership->order_id,
                    'user_id'         => $user->id,
                    'membership_id'   => $membership->id,
                    'plan_id'         => $plan->id,
                    'membership_type' => $plan->membership_type,
                    'type'            => $plan->interval,
                    'price'           => $plan->price,
                    'start_at'        => Carbon::now(),
                    'end_at'          => $endDate,
                ]);

                $user->update(['is_premium' => true]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment successful and membership activated',
                    'membership' => $membership
                ]);
            }

            return response()->json(['success'=>false,'message'=>'Payment failed','response'=>$response],500);

        } catch (\Exception $e) {
            Log::error('PayPal Payment Error: '.$e->getMessage());
            return response()->json(['success'=>false,'message'=>'Exception: '.$e->getMessage()],500);
        }
    }
}
