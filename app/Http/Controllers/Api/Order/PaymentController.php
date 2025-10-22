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

        $sub_total = $cart->CartItems->sum(function ($item) {
            return $item->product->product_price * $item->quantity;
        });

        
        $total_amount = $sub_total + $request->tax_amount + $request->shipping_amount - $request->discount_amount;
        // dd($total_amount);

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
                // Process PayPal payment
            } else {
                return $this->error([], 'Unsupported payment method', 400);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error([], $e->getMessage(), 500);
        }
    }

    public function paymentSuccess(Request $request) {}

    public function paymentCancel(Request $request) {}
}
