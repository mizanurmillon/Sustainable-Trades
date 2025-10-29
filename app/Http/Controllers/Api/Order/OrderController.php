<?php

namespace App\Http\Controllers\Api\Order;

use App\Models\Order;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    use ApiResponse;
    
    public function index(Request $request)
    {
        $user = auth()->user(); 

        $query = Order::with('user:id,first_name,last_name,email')->where('shop_id', $user->shopInfo->id);

        if ($request->query('status')) {
            $query->where('status', $request->status);
        }

        $order = $query->latest()->get();

        if ($order->isEmpty()) {
            return $this->error([], 'No orders found', 200);
        }

        return $this->success($order, 'Orders retrieved successfully');
    }

    public function show(Request $request, $id)
    {
        $user = auth()->user(); 

        $order = Order::with('user:id,first_name,last_name,email,phone,avatar','orderItems','orderItems.product:id,product_name,product_price', 'orderItems.product','shippingAddress', 'OrderStatusHistory')
            ->where('shop_id', $user->shopInfo->id)
            ->where('id', $id)
            ->first();

        if (!$order) {
            return $this->error([], 'Order not found', 404);
        }

        return $this->success($order, 'Order retrieved successfully');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:confirmed,processing,shipped,delivered,cancelled',
        ]);

        $user = auth()->user(); 

        $order = Order::where('shop_id', $user->shopInfo->id)
            ->where('id', $id)
            ->first();

        if (!$order) {
            return $this->error([], 'Order not found', 404);
        }

       try {
            DB::beginTransaction();
            $order->status = $request->status;
            $order->save();

            if(!$order) {
                return $this->error([], 'Failed to update order status', 500);
            }

            if($order->status == 'confirmed') {
                $order->OrderStatusHistory()->create([
                    'order_id' => $order->id,
                    'content' => 'Your order has been confirmed.',
                ]);
            } elseif($order->status == 'processing') {
                $order->OrderStatusHistory()->create([
                    'order_id' => $order->id,
                    'content' => 'Your order is being processed.',
                ]);
            } elseif($order->status == 'shipped') {
                $order->OrderStatusHistory()->create([
                    'order_id' => $order->id,
                    'content' => 'Your order has been shipped.',
                ]);
            } elseif($order->status == 'delivered') {
                $order->OrderStatusHistory()->create([
                    'order_id' => $order->id,
                    'content' => 'Your order has been delivered.',
                ]);
            } elseif($order->status == 'cancelled') {
                $order->OrderStatusHistory()->create([
                    'order_id' => $order->id,
                    'content' => 'Your order has been cancelled.',
                ]);
            }

            DB::commit();
            return $this->success($order, 'Order status updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error([], 'Failed to update order status', 500);
        }
    }

    public function addNote(Request $request, $id)
    {
        $request->validate([
            'note' => 'required|string',
        ]);

        $user = auth()->user();

        $order = Order::where('shop_id', $user->shopInfo->id)
            ->where('id', $id)
            ->first();

        if (!$order) {
            return $this->error([], 'Order not found', 404);
        }

        try {
            DB::beginTransaction();

            $order->note = $request->note;
            $order->save();

            if(!$order) {
                return $this->error([], 'Failed to add order note', 500);
            }

            DB::commit();
            return $this->success($order, 'Order note added successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error([], 'Failed to add order note', 500);
        }
    }
}
