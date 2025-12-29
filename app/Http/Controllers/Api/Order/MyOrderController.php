<?php

namespace App\Http\Controllers\Api\Order;

use App\Models\Order;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MyOrderController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Order::with('shop:id,user_id,shop_name,shop_image', 'shop.user:id,first_name,last_name', 'shop.user.membership', 'orderItems', 'orderItems.product:id,product_name,product_price', 'orderItems.product.images')->where('user_id', $user->id);

        if ($request->query('status')) {
            $query->where('status', $request->status);
        }

        $order = $query->latest()->get();

        if ($order->isEmpty()) {
            return $this->error([], 'No orders found', 200);
        }

        return $this->success($order, 'Orders retrieved successfully');
    }

    public function show($id)
    {
        $user = auth()->user();

        $order = Order::with('shippingAddress', 'paymentHistory', 'shop:id,user_id,shop_name,shop_image', 'orderItems', 'orderItems.product:id,product_name,product_price', 'orderItems.product.images')
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$order) {
            return $this->error([], 'Order not found', 404);
        }

        return $this->success($order, 'Order retrieved successfully');
    }

    public function orderHistory($id)
    {
        $user = auth()->user();

        $order = Order::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$order) {
            return $this->error([], 'Order not found', 404);
        }

        $history = $order->OrderStatusHistory()->get();

        return $this->success($history, 'Order history retrieved successfully');
    }
}
