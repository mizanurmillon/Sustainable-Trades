<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\MyFavorit;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AllProductController extends Controller
{
    use ApiResponse;

    public function allProducts(Request $request) {
        
        $query = Product::with('images', 'shop:id,user_id,shop_name','shop.address')->where('status', 'approved')->select('id','shop_info_id','product_name', 'product_price', 'product_quantity', 'unlimited_stock', 'out_of_stock', 'selling_option');

        if($request->has('search')) {
            $query->where('product_name', 'like', '%' . $request->search . '%');
        }

        $data = $query->get();

        if ($data->isEmpty()) {
            return $this->error([], 'No products found', 404);
        }

        return $this->success($data,'Data fetched successfully',200);
    }

    public function isFeaturedProduct()
    {
        $data = Product::with('images')->where('is_featured', true)->where('status', 'approved')->get();

        if ($data->isEmpty()) {
            return $this->error([], 'No featured products found', 404);
        }

        // If user is authenticated, fetch favorite products
        $favorites = [];
        if (auth()->user()) {
            $favorites = MyFavorit::where('user_id', auth()->id())
                ->whereIn('product_id', $data->pluck('id'))
                ->pluck('product_id')
                ->toArray(); // Get only the product IDs
        }

        // Attach `is_favorite` flag to each product
        foreach ($data as $product) {
            $product->is_favorite = in_array($product->id, $favorites);
        }

        return $this->success($data,'Data fetched successfully',200);
    }
}
