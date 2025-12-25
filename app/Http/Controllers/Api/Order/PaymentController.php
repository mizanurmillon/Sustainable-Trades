<?php

namespace App\Http\Controllers\Api\Order;

use App\Models\Cart;
use App\Models\Order;
use App\Enum\OrderStatus;
use App\Models\OrderItem;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\shippingAddress;
use App\Models\OrderStatusHistory;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use App\Service\PayPalClient;
use PaypalServerSdkLib\Models\Builders\OrderRequestBuilder;
use PaypalServerSdkLib\Models\CheckoutPaymentIntent;
use PaypalServerSdkLib\Models\Builders\PurchaseUnitRequestBuilder;
use PaypalServerSdkLib\Models\Builders\AmountWithBreakdownBuilder;
use PaypalServerSdkLib\Models\Builders\MoneyBuilder;
use PaypalServerSdkLib\Models\Builders\PaymentSourceBuilder;
use PaypalServerSdkLib\Models\Builders\PaypalWalletBuilder;
use PaypalServerSdkLib\Models\Builders\PaypalWalletExperienceContextBuilder;
use PaypalServerSdkLib\Models\PaypalExperienceUserAction;
use PaypalServerSdkLib\Models\ShippingPreference;
use PaypalServerSdkLib\Models\Payee;

class PaymentController extends Controller
{
    use ApiResponse;

    public function checkout(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'shipping_amount' => 'nullable|numeric',
            'discount_amount' => 'nullable|numeric',
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

        if (!$user) {
            return $this->error([], 'User not found', 404);
        }

        $cart = Cart::with('CartItems')->where('id', $id)->first();

        if (!$cart) {
            return $this->error([], 'Cart not found', 404);
        }

        if ($cart->shop->shopTax->country == $request->country && $cart->shop->shopTax->state == $request->state) {
            $tax_rate = $cart->shop->shopTax->rate;
            $sub_total = $cart->CartItems->sum(fn($item) => $item->product->product_price * $item->quantity);
            $calculated_tax = ($sub_total * $tax_rate) / 100;
        } else {
            $calculated_tax = 0;
        }
        // dd($calculated_tax);

        $sub_total = $cart->CartItems->sum(fn($item) => $item->product->product_price * $item->quantity);
        $total_amount = $sub_total + ($request->shipping_amount ?? 0) + ($calculated_tax ?? 0) - ($request->discount_amount ?? 0);

        try {
            DB::beginTransaction();
            // Payment processing logic goes here
            if ($request->payment_method == 'cash_on_delivery') {
                $data = Order::create([
                    'user_id' => $user->id,
                    'shop_id' => $cart->shop_id,
                    'order_number' => 'ORD' . time(),
                    'total_quantity' => $cart->CartItems->sum('quantity'),
                    'sub_total' => $sub_total,
                    'total_amount' => $total_amount,
                    'tax_amount' => $calculated_tax ?? 0,
                    'shipping_amount' => $request->shipping_amount,
                    'discount_amount' => $request->discount_amount,
                    'payment_method' => $request->payment_method,
                    'payment_status' => 'pending',
                    'currency' => 'USD',
                    'shipping_option' => $request->shipping_option ?? 'delivery',
                    'status' => 'pending',
                ]);

                foreach ($cart->CartItems as $item) {
                    OrderItem::create([
                        'order_id' => $data->id,
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->product->product_price,
                        'total_price' => $item->product->product_price * $item->quantity,
                    ]);
                }

                shippingAddress::create([
                    'order_id' => $data->id,
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
                    'order_id' => $data->id,
                    'content' => 'Order Created',
                ]);

                // Clear the cart after order placement
                $cart->CartItems()->delete();
                $cart->delete();

                DB::commit();

                return $this->success($data, 'Order placed successfully', 200);
            } elseif ($request->payment_method === 'paypal') {

                $order = Order::create([
                    'user_id' => $user->id,
                    'shop_id' => $cart->shop_id,
                    'order_number' => 'ORD' . time(),
                    'total_quantity' => $cart->CartItems->sum('quantity'),
                    'sub_total' => $sub_total,
                    'total_amount' => $total_amount,
                    'tax_amount' => $calculated_tax ?? 0,
                    'shipping_amount' => $request->shipping_amount ?? 0,
                    'discount_amount' => $request->discount_amount ?? 0,
                    'payment_method' => 'paypal',
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

                $client = PayPalClient::client();

                // Format total amount for PayPal (2 decimal places)
                $formattedAmount = number_format($total_amount, 2, '.', '');

                // Create amount with breakdown - CORRECTED: Pass 2 arguments
                $amount = AmountWithBreakdownBuilder::init(
                    "USD",  // currency code
                    $formattedAmount  // value
                )->build();

                // Create purchase unit
                $purchaseUnit = PurchaseUnitRequestBuilder::init($amount)
                    ->referenceId((string) $order->id)
                    ->description("Order #" . $order->order_number)
                    ->build();

                // Create PayPal order request
                $orderRequest = OrderRequestBuilder::init(
                    CheckoutPaymentIntent::CAPTURE,
                    [$purchaseUnit]
                )
                    ->paymentSource(
                        PaymentSourceBuilder::init()
                            ->paypal(
                                PaypalWalletBuilder::init()
                                    ->experienceContext(
                                        PaypalWalletExperienceContextBuilder::init()
                                            ->userAction(PaypalExperienceUserAction::PAY_NOW)
                                            // ->returnUrl(route('success.payment', ['order_id' => $order->id]))
                                            ->cancelUrl(route('payment.cancel', ['order_id' => $order->id]))
                                            ->build()
                                    )
                                    ->build()
                            )
                            ->build()
                    )
                    ->build();

                // Create the PayPal order
                $ordersController = $client->getOrdersController();

                $options = [
                    'body' => $orderRequest,
                    'prefer' => 'return=representation',
                ];

                $ordersController = $client->getOrdersController();

                $options = [
                    'body' => $orderRequest,
                    'prefer' => 'return=representation',
                ];

                $response = $ordersController->createOrder($options);

                // dd($response);

                // Check if response was successful
                if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                    $paypalOrder = $response->getResult();

                    // dd($paypalOrder);

                    // Store PayPal order ID in your database – FIXED
                    $order->update([
                        'paypal_order_id' => $paypalOrder->getId(),          // Use getter
                        'paypal_order_status' => $paypalOrder->getStatus(),  // Use getter
                    ]);
                    DB::commit();

                    // Clear the cart
                    $cart->CartItems()->delete();
                    $cart->delete();

                    return response()->json([
                        'status' => true,
                        'message' => 'PayPal order created successfully',
                        'order_id' => $order->id,
                        'paypal_order_id' => $paypalOrder->getId(),
                        'approve_link' => $paypalOrder->getLinks(),
                    ]);
                } else {
                    throw new \Exception('Failed to create PayPal order. Status: ' . $response->getStatusCode());
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
