<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class TopVendorController extends Controller
{
    use ApiResponse;
    
    public function topVendors()
    {
        $query = User::with('shopInfo:id,user_id,shop_name,shop_image,shop_banner', 'shopInfo.address')
            ->where('role', 'vendor')
            ->where('status', 'active')
            ->whereHas('shopInfo')
            ->select('id', 'first_name', 'last_name', 'role', 'avatar');

        $data = $query->get();

        if ($data->isEmpty()) {
            return $this->error([], 'No shops found', 200);
        }

        return $this->success($data, 'All top shops retrieved successfully', 200);
    }
}
