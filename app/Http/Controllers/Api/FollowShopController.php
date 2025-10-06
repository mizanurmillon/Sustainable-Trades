<?php

namespace App\Http\Controllers\Api;

use App\Models\FollowShop;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FollowShopController extends Controller
{
    use ApiResponse;

    public function followShops()
    {
        $user = auth()->user();

        if(!$user){
           return $this->error([], "User Unauthorized", 401); 
        }

        $data = FollowShop::where('user_id', $user->id)->with('shop:id,user_id,shop_name,shop_image', 'shop.address')->latest()->get();

        return $this->success($data, "Followed Shops", 200);
    }

    public function followShop(Request $request, $id)
    {
        $user = auth()->user();

        if(!$user) {
            return $this->error([], "User Unauthorized", 401); 
        }

        $existingFollow = FollowShop::where('user_id', $user->id)
            ->where('shop_info_id', $id)
            ->first();

        if($existingFollow){
            $existingFollow->delete();
            return $this->success([], "Unfollowed", 200);
        }else{
            $data = FollowShop::create([
                "user_id"=> $user->id,
                "shop_info_id"=> $id,
            ]);

            if(!$data){
                return $this->error([], "Failed to Follow shop", 500);
            }
            return $this->success($data, "Successfully Followed", 200);
        }
    }
}
