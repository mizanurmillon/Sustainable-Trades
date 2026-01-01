<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Models\Order;
use App\Models\TradeOffer;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    use ApiResponse;

    public function index()
    {
        // Logic to gather dashboard data for the vendor

        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'User not found', 404);
        }

        $totalOrder = Order::where('shop_id', $user->shopInfo->id)->count();

        $totalTrades = TradeOffer::where('receiver_id', $user->id)
            ->orWhere('sender_id', $user->id)
            ->count();

        $totalRevenue = Order::where('shop_id', $user->shopInfo->id)
            ->where('status', 'delivered')
            ->sum('total_amount');

        $data = [
            'total_orders' => $totalOrder,
            'total_trades' => $totalTrades,
            'total_revenue' => $totalRevenue,
        ];

        return $this->success($data, 'Vendor dashboard data retrieved successfully', 200);
    }
}
