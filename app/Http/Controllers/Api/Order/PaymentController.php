<?php

namespace App\Http\Controllers\Api\Order;

use App\Models\Cart;
use App\Models\Order;
use App\Enum\OrderStatus;
use App\Models\OrderItem;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\PaypalAccount;
use App\Service\PayPalClient;
use App\Models\shippingAddress;
use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use PayPalCheckoutSdk\Payouts\PayoutsPostRequest;
use PaypalServerSdkLib\Models\CheckoutPaymentIntent;
use PaypalServerSdkLib\Models\PaypalExperienceUserAction;
use PaypalServerSdkLib\Models\Builders\OrderRequestBuilder;
use PaypalServerSdkLib\Models\Builders\PaypalWalletBuilder;
use PaypalServerSdkLib\Models\Builders\PaymentSourceBuilder;
use PaypalServerSdkLib\Models\Builders\AmountWithBreakdownBuilder;
use PaypalServerSdkLib\Models\Builders\PurchaseUnitRequestBuilder;
use PaypalServerSdkLib\Models\Builders\PaypalWalletExperienceContextBuilder;

class PaymentController extends Controller
{
    use ApiResponse;

    public function checkout(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|string',
            'shipping_option' => 'nullable|in:pickup,delivery',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'apt' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), $validator->errors()->first(), 422);
        }

        $user = auth()->user();
        if (!$user) return $this->error([], 'User not found', 404);

        $cart = Cart::with('CartItems.product', 'shop.shopTax', 'shop.weightRangeRates')->where('id', $id)->first();
        if (!$cart) return $this->error([], 'Cart not found', 404);

        // Tax
        $tax_rate = 0;
        if ($cart->shop->shopTax && $cart->shop->shopTax->country == $request->country && $cart->shop->shopTax->state == $request->state) {
            $tax_rate = $cart->shop->shopTax->rate;
        }
        $sub_total = $cart->CartItems->sum(fn($item) => $item->product->product_price * $item->quantity);
        $calculated_tax = ($sub_total * $tax_rate) / 100;

        // Shipping
        $product_weight = (float) $cart->CartItems->sum(fn($item) => $item->product->weight * $item->quantity);
        $rate = $cart->shop->weightRangeRates->first(fn($rate) => (float)$rate->min_weight <= $product_weight && (float)$rate->max_weight >= $product_weight);
        $shipping_cost = $rate ? (float)$rate->cost : 0;

        $total_amount = $sub_total + $shipping_cost + $calculated_tax;

        DB::beginTransaction();
        try {
            // Order creation
            $order = Order::create([
                'user_id' => $user->id,
                'shop_id' => $cart->shop_id,
                'order_number' => 'ORD' . time(),
                'total_quantity' => $cart->CartItems->sum('quantity'),
                'sub_total' => $sub_total,
                'total_amount' => $total_amount,
                'tax_amount' => $calculated_tax,
                'shipping_amount' => $shipping_cost,
                'discount_amount' => '0.00',
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
                'currency' => 'USD',
                'shipping_option' => $request->shipping_option ?? 'delivery',
                'status' => 'pending',
            ]);

            foreach ($cart->CartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->product->product_price,
                    'total_price' => $item->product->product_price * $item->quantity,
                ]);
            }

            shippingAddress::create([
                'order_id' => $order->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'postal_code' => $request->postal_code,
                'country' => $request->country,
                'apt' => $request->apt,
                'phone' => $request->phone,
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'content' => 'Order Created',
            ]);

            // Cash on delivery
            if ($request->payment_method == 'cash_on_delivery') {
                $order->update(['payment_status' => 'pending']);
                $cart->CartItems()->delete();
                $cart->delete();
                DB::commit();
                return $this->success($order, 'Order placed successfully', 200);
            }

            // PayPal payment
            if ($request->payment_method == 'paypal') {
                $client = PayPalClient::client(); // <-- Sandbox / Live correctly configured
                $formattedAmount = number_format($total_amount, 2, '.', '');

                $amount = AmountWithBreakdownBuilder::init("USD", $formattedAmount)->build();
                $purchaseUnit = PurchaseUnitRequestBuilder::init($amount)
                    ->referenceId((string)$order->id)
                    ->description("Order #" . $order->order_number)
                    ->build();

                $orderRequest = OrderRequestBuilder::init(CheckoutPaymentIntent::CAPTURE, [$purchaseUnit])
                    ->paymentSource(
                        PaymentSourceBuilder::init()
                            ->paypal(
                                PaypalWalletBuilder::init()
                                    ->experienceContext(
                                        PaypalWalletExperienceContextBuilder::init()
                                            ->userAction(PaypalExperienceUserAction::PAY_NOW)
                                            ->cancelUrl(route('payment.cancel', ['order_id' => $order->id]))
                                            ->build()
                                    )
                                    ->build()
                            )
                            ->build()
                    )
                    ->build();

                $ordersController = $client->getOrdersController();
                $response = $ordersController->createOrder(['body' => $orderRequest, 'prefer' => 'return=representation']);

                if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                    $paypalOrder = $response->getResult();
                    $order->update([
                        'paypal_order_id' => $paypalOrder->getId(),
                        'paypal_order_status' => $paypalOrder->getStatus(),
                    ]);

                    // Vendor Payout
                    $vendorAccount = PaypalAccount::where('user_id', $cart->shop->user_id)
                        ->where('paypal_email_verified', 1)
                        ->first();

                    if ($vendorAccount) {
                        $vendorEmail = $vendorAccount->paypal_email;

                        $payouts = new PayoutsPostRequest();
                        $payouts->body = [
                            "sender_batch_header" => [
                                "sender_batch_id" => uniqid(),
                                "email_subject" => "You have a payout!",
                                "email_message" => "You have received a payout for your order."
                            ],
                            "items" => [
                                [
                                    "recipient_type" => "EMAIL",
                                    "receiver" => $vendorEmail,
                                    "amount" => [
                                        "value" => $formattedAmount,
                                        "currency" => "USD"
                                    ],
                                    "note" => "Order payment for order #" . $order->order_number,
                                    "sender_item_id" => $order->id
                                ]
                            ]
                        ];

                        $client->execute($payouts); // <-- This will send payout to verified vendor email
                    }

                    // Clear Cart
                    $cart->CartItems()->delete();
                    $cart->delete();

                    DB::commit();

                    return response()->json([
                        'status' => true,
                        'message' => 'PayPal order created and vendor payout sent successfully',
                        'order_id' => $order->id,
                        'paypal_order_id' => $paypalOrder->getId(),
                        'approve_link' => $paypalOrder->getLinks(),
                    ]);
                } else {
                    throw new \Exception('Failed to create PayPal order.');
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error([], $e->getMessage(), 500);
        }
    }



    public function paymentSuccess(Request $request)
    {
        try {
            $request->validate([
                'paypal_order_id' => 'required|string',
            ]);

            // PayPal Order ID from frontend
            $paypalOrderId = $request->paypal_order_id;

            // Find order using paypal_order_id
            $order = Order::where('paypal_order_id', $paypalOrderId)->firstOrFail();

            $client = PayPalClient::client();

            // Capture PayPal order
            $response = $client->getOrdersController()->captureOrder([
                'id' => $paypalOrderId
            ]);

            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {

                $result = $response->getResult();

                $paypalStatus = $result->getStatus();

                $order->update([
                    'payment_status' => 'completed',
                    'status' => 'pending', // better than pending
                    'paypal_order_status' => $paypalStatus,
                ]);

                //Capture ID safely
                $captureId = null;
                $purchaseUnits = $result->getPurchaseUnits();

                if (
                    !empty($purchaseUnits) &&
                    !empty($purchaseUnits[0]->getPayments()) &&
                    !empty($purchaseUnits[0]->getPayments()->getCaptures())
                ) {
                    $captureId = $purchaseUnits[0]->getPayments()->getCaptures()[0]->getId();
                    $order->update(['paypal_capture_id' => $captureId]);
                }

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'content' => 'Payment completed via PayPal. Capture ID: ' . ($captureId ?? 'N/A'),
                ]);

                return $this->success($order, 'Payment successful');
            }

            return $this->error([], 'Payment capture failed', 500);
        } catch (\Exception $e) {
            logger()->error('PayPal Capture Error', [
                'error' => $e->getMessage()
            ]);
            return $this->error([], $e->getMessage(), 500);
        }
    }


    public function paymentCancel(Request $request, $order_id)
    {
        try {
            $order = Order::findOrFail($order_id);

            // Update status
            $order->update([
                'payment_status' => 'cancelled',
                'status' => 'cancelled',
                'paypal_order_status' => 'CANCELLED',
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'content' => 'Payment canceled by user from PayPal'
            ]);

            return $this->success($order, 'Payment canceled successfully', 200);
        } catch (\Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }
}
