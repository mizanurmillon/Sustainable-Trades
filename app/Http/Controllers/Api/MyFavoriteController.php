<?php

namespace App\Http\Controllers\Api;

use App\Models\MyFavorit;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Product;

class MyFavoriteController extends Controller
{
    use ApiResponse;

    public function myFavorites()
    {
        $user = auth()->user();

        if(!$user){
           return $this->error([], "User Unauthorized", 401); 
        }

        $data = MyFavorit::where('user_id', $user->id)->with('product:id,product_name,product_price,product_quantity,is_featured,out_of_stock,selling_option,unlimited_stock','product.images')->latest()->get();

        return $this->success($data, "My Favorites", 200);
    }

    public function addFavorite(Request $request, $id)
    {
        $user = auth()->user();

        if(!$user){
           return $this->error([], "User Unauthorized", 401); 
        }

        $product = Product::find($id);

        if(!$product){
            return $this->error([], "Product not found", 404);
        }

        $existingFavorite = MyFavorit::where('user_id', $user->id)
            ->where('product_id', $id)
            ->first();

        if($existingFavorite){
            $existingFavorite->delete();
            return $this->success([], "Removed from favorites", 200);
        }else{
            $data = MyFavorit::create([
                "user_id"=> $user->id,
                "product_id"=> $id,
                "shop_info_id"=> $product->shop_info_id,
            ]);

            if(!$data){
                return $this->error([], "Failed to add to favorites", 500);
            }

            return $this->success($data, "Added to favorites", 200);
        }
    }
}
