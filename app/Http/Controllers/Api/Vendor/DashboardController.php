<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Models\Order;
use App\Models\TradeOffer;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ShopVisit;

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

        $totalVisits = ShopVisit::where('shop_id', $user->shopInfo->id)->count();

        $data = [
            'total_orders' => $totalOrder,
            'total_trades' => $totalTrades,
            'total_revenue' => $totalRevenue,
            'total_visits' => $totalVisits,
        ];

        return $this->success($data, 'Vendor dashboard data retrieved successfully', 200);
    }

    public function getVisits()
    {
        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'User not found', 404);
        }

        $totalVisits = ShopVisit::where('shop_id', $user->shopInfo->id)->count();
        $lastMonthVisits = ShopVisit::where('shop_id', $user->shopInfo->id)
            ->whereMonth('visited_at', now()->subMonth()->month)
            ->whereYear('visited_at', now()->subMonth()->year)
            ->count();

        $todayVisits = ShopVisit::where('shop_id', $user->shopInfo->id)
            ->whereDate('visited_at', today())
            ->count();

        $data = [
            'total_visits' => $totalVisits,
            'last_month_visits' => $lastMonthVisits,
            'today_visits' => $todayVisits
        ];

        return $this->success($data, 'Shop visits retrieved successfully', 200);
    }

    public function getOrderStats()
    {
        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'User not found', 404);
        }
        $pendingOrders = Order::where('shop_id', $user->shopInfo->id)
            ->where('status', 'pending')
            ->count();
        $shippedOrders = Order::where('shop_id', $user->shopInfo->id)
            ->where('status', 'shipped')
            ->count();
        $deliveredOrders = Order::where('shop_id', $user->shopInfo->id)
            ->where('status', 'delivered')
            ->count();

        $data = [
            'new_orders' => $pendingOrders,
            'shipped_orders' => $shippedOrders,
            'completed_orders' => $deliveredOrders,
        ];

        return $this->success($data, 'Order statistics retrieved successfully', 200);
    }

    public function getListingsStats()
    {
        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'User not found', 404);
        }

        $activeListings = Product::where('shop_info_id', $user->shopInfo->id)->where('status', 'approved')->count();
        $inactiveListings = Product::where('shop_info_id', $user->shopInfo->id)->where('status', 'pending')->count();
        $soldOutListings = Product::where('shop_info_id', $user->shopInfo->id)->where('status', 'approved')->whereNot('unlimited_stock', true)->where('product_quantity', 0)->count();

        $data = [
            'active_listings' => $activeListings,
            'inactive_listings' => $inactiveListings,
            'sold_out_listings' => $soldOutListings,
        ];

        return $this->success($data, 'Listings statistics retrieved successfully', 200);
    }

    public function getTradesStats()
    {
        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'User not found', 404);
        }

        $pendingTrades = TradeOffer::where(function ($query) use ($user) {
            $query->where('receiver_id', $user->id)
                ->orWhere('sender_id', $user->id);
        })->where('status', 'pending')->count();

        $acceptedTrades = TradeOffer::where(function ($query) use ($user) {
            $query->where('receiver_id', $user->id)
                ->orWhere('sender_id', $user->id);
        })->where('status', 'accepted')->count();

        $completedTrades = TradeOffer::where(function ($query) use ($user) {
            $query->where('receiver_id', $user->id)
                ->orWhere('sender_id', $user->id);
        })->where('status', 'completed')->count();

        $data = [
            'pending_trades' => $pendingTrades,
            'accepted_trades' => $acceptedTrades,
            'completed_trades' => $completedTrades,
        ];

        return $this->success($data, 'Trade statistics retrieved successfully', 200);
    }
}
