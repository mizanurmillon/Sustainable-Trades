<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShopImageAndBannerController extends Controller
{
    use ApiResponse;
    
    public function shopImageUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shop_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10240',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), $validator->errors()->first(), 422);
        }
       
        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'User not found', 404);
        }

        $shop = $user->shopInfo;

        if (!$shop) {
            return $this->error([], 'Shop not found', 404);
        }

        if ($request->hasFile('shop_image')) {
            // Delete the old image if it exists
            if(file_exists(public_path($shop->shop_image))){
                unlink(public_path($shop->shop_image));
            }
            $image = $request->file('shop_image');
            $imageName = uploadImage($image, 'shops');
        }else{
            // If no new image is uploaded, keep the old image
            $imageName = $shop->shop_image;
        }

        $shop->shop_image = $imageName;
        $shop->save();

        return $this->success($shop, 'Shop image updated successfully', 200);

    }

    public function shopBannerUpdate(Request $request)
    {
        $request->validate([
            'shop_banner' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'User not found', 404);
        }

        $shop = $user->shopInfo;

        if (!$shop) {
            return $this->error([], 'Shop not found', 404);
        }

        if ($request->hasFile('shop_banner')) {
            // Delete the old banner if it exists
            if(file_exists(public_path($shop->shop_banner))){
                unlink(public_path($shop->shop_banner));
            }
            $banner = $request->file('shop_banner');
            $bannerName = uploadImage($banner, 'shops/banners');
        }else{
            // If no new banner is uploaded, keep the old banner
            $bannerName = $shop->shop_banner;
        }

        $shop->shop_banner = $bannerName;
        $shop->save();

        return $this->success($shop, 'Shop banner updated successfully', 200);

    }
}
