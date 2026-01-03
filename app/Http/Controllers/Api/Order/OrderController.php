<?php

namespace App\Http\Controllers\Api\Order;

use App\Models\Order;
use App\Traits\ApiResponse;
use Faker\Provider\Payment;
use Illuminate\Http\Request;
use App\Models\PaymentHistory;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Notifications\OrderNotification;

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

    public function show($id)
    {
        $user = auth()->user();

        $order = Order::with('user:id,first_name,last_name,email,phone,avatar,role', 'user.membership', 'orderItems', 'orderItems.product:id,product_name,product_price', 'orderItems.product.images', 'shippingAddress', 'OrderStatusHistory')
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

        if ($order->status == $request->status) {
            return $this->error([], 'Order status is already ' . $request->status, 400);
        }

        if ($order->status == 'delivered') {
            return $this->error([], 'Order has already been delivered', 400);
        }

        if ($order->status == 'cancelled') {
            return $this->error([], 'Order has already been cancelled', 400);
        }

        if ($request->status == 'processing' && $order->status != 'confirmed') {
            return $this->error([], 'Order must be confirmed before it can be processed', 400);
        }

        if ($request->status == 'shipped' && $order->status != 'processing') {
            return $this->error([], 'Order must be processed before it can be shipped', 400);
        }

        if ($request->status == 'cancelled' && $order->status == 'shipped') {
            return $this->error([], 'Shipped orders cannot be cancelled', 400);
        }

        if ($request->status == 'delivered' && $order->status != 'shipped') {
            return $this->error([], 'Order must be shipped before it can be marked as delivered', 400);
        }

        /* ---------------------------------
        | Status history messages
        ---------------------------------*/
        $statusMessages = [
            'confirmed'  => 'Order confirmed.',
            'processing' => 'Order is being processed.',
            'shipped'    => 'Order has been shipped.',
            'delivered'  => 'Order has been delivered.',
            'cancelled'  => 'Order has been cancelled.',
        ];

        if ($order->OrderStatusHistory()->where('content', $statusMessages[$request->status])->exists()) {
            return $this->error([], 'Order has already been updated to ' . $request->status . ' status', 400);
        }

        try {
            DB::beginTransaction();

            // Update order status
            $order->update([
                'status' => $request->status,
            ]);

            if ($order->payment_method == 'cash_on_delivery' && $request->status == 'delivered') {

                $order->update([
                    'payment_status' => 'completed',
                ]);

                $order->paymentHistory()->update([
                    'payment_status' => 'completed',
                ]);
            }

            // Save status history
            $order->OrderStatusHistory()->create([
                'order_id' => $order->id,
                'content'  => $statusMessages[$request->status],
            ]);

            DB::commit();

            $order->user->notify(new OrderNotification(
                subject: 'Order status updated',
                message: 'Your order status has been updated to ' . $request->status . '.',
                type: 'success',
                order: $order
            ));

            return $this->success($order, 'Order status updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->error(
                ['error' => $e->getMessage()],
                'Failed to update order status',
                500
            );
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

            if (!$order) {
                return $this->error([], 'Failed to add order note', 500);
            }

            DB::commit();
            return $this->success($order, 'Order note added successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error([], 'Failed to add order note', 500);
        }
    }

    public function paymentReport(Request $request)
    {
        $user = auth()->user();

        $query = PaymentHistory::with('order:id,user_id,shop_id,order_number,created_at', 'order.user:id,first_name,last_name,email')->whereHas('order', function ($q) use ($user) {
            $q->where('shop_id', $user->shopInfo->id);
        });

        if ($request->query('status')) {
            $query->where('payment_status', $request->status);
        }

        $payments = $query->latest()->get();

        if ($payments->isEmpty()) {
            return $this->error([], 'No payment records found', 200);
        }

        return $this->success($payments, 'Payment records retrieved successfully');
    }
}
