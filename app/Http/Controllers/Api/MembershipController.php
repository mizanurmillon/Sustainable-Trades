<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Membership;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;
use App\Models\MembershipHistory;
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
            'expiry_date'      => 'required|string|regex:/^\d{2}\/\d{2}$/', // Validate MM/YY
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
            Log::info('PayPal Config:', ['config' => config('paypal')]);
            $provider->setApiCredentials(config('paypal'));

            // Get access token
            $tokenResponse = $provider->getAccessToken();
            Log::info('PayPal getAccessToken Response:', ['tokenResponse' => $tokenResponse]);

            if (!isset($tokenResponse['access_token'])) {
                Log::error('Failed to get PayPal access token', ['response' => $tokenResponse]);
                throw new \Exception('Unable to retrieve PayPal access token');
            }

            $token = $tokenResponse['access_token'];

            // Transform expiry_date from MM/YY to YYYY-MM
            $expiryParts = explode('/', $request->expiry_date);
            if (count($expiryParts) !== 2) {
                return response()->json(['success' => false, 'message' => 'Invalid expiry date format'], 422);
            }
            $month = $expiryParts[0];
            $year = '20' . $expiryParts[1]; // Convert YY to YYYY
            $formattedExpiry = "$year-$month";

            // Log card details (sanitized)
            Log::info('PayPal Card Details:', [
                'card_holder_name' => $request->card_holder_name,
                'card_number' => $request->card_number,
                'expiry' => $formattedExpiry,
                'cvv' => $request->cvv,
            ]);

            // Create order
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
                'PayPal-Request-Id' => (string) Str::uuid(),
            ])->post('https://api-m.sandbox.paypal.com/v2/checkout/orders', [
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
                        'expiry' => $formattedExpiry,
                        'security_code' => $request->cvv,
                        'name' => $request->card_holder_name,
                    ]
                ]
            ]);

            $data = $response->json();
            Log::info('PayPal Order Response:', ['data' => $data]);

            if (isset($data['status']) && $data['status'] === 'COMPLETED') {
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

            Log::error('PayPal Order Creation Failed:', ['response' => $data]);
            return response()->json(['success' => false, 'message' => 'Payment failed', 'response' => $data], 500);
        } catch (\Exception $e) {
            Log::error('PayPal Payment Error:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Exception: ' . $e->getMessage()], 500);
        }
    }
}
