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

class PaymentController extends Controller
{
    use ApiResponse;

    public function checkout(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'shipping_amount' => 'nullable|numeric',
            'discount_amount' => 'nullable|numeric',
            'tax_amount' => 'nullable|numeric',
            'payment_method' => 'required|string',
            'shipping_option' => 'nullable|in:pickup,delivery',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
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

        $sub_total = $cart->CartItems->sum(fn($item) => $item->product->product_price * $item->quantity);
        $total_amount = $sub_total + ($request->tax_amount ?? 0) + ($request->shipping_amount ?? 0) - ($request->discount_amount ?? 0);

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
                    'tax_amount' => $request->tax_amount,
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
            } elseif ($request->payment_method == 'paypal') {
                // 1. Create order in DB with pending status
                $order = Order::create([
                    'user_id' => $user->id,
                    'shop_id' => $cart->shop_id,
                    'order_number' => 'ORD' . time(),
                    'total_quantity' => $cart->CartItems->sum('quantity'),
                    'sub_total' => $sub_total,
                    'total_amount' => $total_amount,
                    'tax_amount' => $request->tax_amount,
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
                        'order_id' => $order->id,
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->product->product_price,
                        'total_price' => $item->product->product_price * $item->quantity,
                    ]);
                }

                // Save shipping info
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

                // PayPal setup
                $clientId = config('services.paypal.sandbox.client_id');
                $clientSecret = config('services.paypal.sandbox.client_secret');
                $environment = new SandboxEnvironment($clientId, $clientSecret);
                $client = new PayPalHttpClient($environment);

                $paypalOrder = new OrdersCreateRequest();
                $paypalOrder->prefer('return=representation');
                $paypalOrder->body = [
                    "intent" => "CAPTURE",
                    "purchase_units" => [[
                        "amount" => [
                            "currency_code" => "USD",
                            "value" => $total_amount
                        ],
                        "payee" => [
                            // "email_address" => $order->shop->user->paypal_email,
                            "merchant_id" => $order->shop->user->PaypalAccount->paypal_merchant_id
                        ]
                    ]],
                    "application_context" => [
                        "cancel_url" => route('payment.cancel', ['order_id' => $order->id]),
                        "return_url" => route('success.payment', ['order_id' => $order->id])
                    ]
                ];

                $response = $client->execute($paypalOrder);

                DB::commit();

                return $this->success([
                    'order_id' => $order->id,
                    'paypal_approval_url' => collect($response->result->links)->firstWhere('rel', 'approve')->href
                ], 'PayPal order created successfully', 200);
            } else {
                return $this->error([], 'Unsupported payment method', 400);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error([], $e->getMessage(), 500);
        }
    }

    public function paymentSuccess(Request $request)
    {
        $order_id = $request->query('order_id');
        $token = $request->query('token'); // PayPal Order ID

        $order = Order::find($order_id);
        if (!$order) return $this->error([], 'Order not found', 404);

        $client = new PayPalHttpClient(new SandboxEnvironment(
            config('services.paypal.sandbox.client_id'),
            config('services.paypal.sandbox.client_secret')
        ));

        // First: Read the order details
        $getRequest = new \PayPalCheckoutSdk\Orders\OrdersGetRequest($token);
        $getResponse = $client->execute($getRequest);

        $status = $getResponse->result->status;

        // If already captured → do NOT capture again
        if ($status === "COMPLETED") {

            $order->update([
                'payment_status' => 'completed',
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'content' => 'Payment already completed via PayPal',
            ]);

            return $this->success($order, 'Payment already completed', 200);
        }

        // Otherwise capture
        $captureRequest = new OrdersCaptureRequest($token);
        $captureRequest->prefer('return=representation');

        try {
            $response = $client->execute($captureRequest);

            $order->update([
                'payment_status' => 'completed',
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'content' => 'Payment completed via PayPal',
            ]);

            // Clear cart items
            if ($order->cart) {
                $order->cart->CartItems()->delete();
                $order->cart()->delete();
            }

            return $this->success($order, 'Payment successful', 200);
        } catch (\Exception $e) {

            return $this->error([], $e->getMessage(), 500);
        }
    }


    public function paymentCancel(Request $request)
    {
        $order_id = $request->query('order_id');

        if (!$order_id) {
            return $this->error([], 'Order ID missing in cancel URL', 400);
        }

        $order = Order::find($order_id);

        if (!$order) {
            return $this->error([], 'Order not found', 404);
        }

        // Update status
        $order->update([
            'payment_status' => 'cancelled',
            'status' => 'cancelled',
        ]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'content' => 'Payment canceled by user from PayPal'
        ]);

        return $this->success($order, 'Payment canceled successfully', 200);
    }
}
