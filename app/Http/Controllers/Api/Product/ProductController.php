<?php

namespace App\Http\Controllers\Api\Product;

use App\Models\Product;
use App\Models\Category;
use App\Models\MyFavorit;
use App\Models\FollowShop;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    use ApiResponse;

    public function allProducts(Request $request)
    {
        $data = Category::where('status', 'active')
            ->whereHas('products', function ($query) {
                $query->where('status', 'approved');
            })
            ->with([
                'products' => function ($query) {
                    $query->where('status', 'approved')
                        ->select('id', 'category_id', 'sub_category_id', 'shop_info_id', 'product_name', 'product_price', 'product_quantity', 'unlimited_stock', 'out_of_stock', 'selling_option');
                },
                'products.images'
            ])
            ->get();

        if ($data->isEmpty()) {
            return $this->error([], 'No categories found', 200);
        }

        // Collect all product IDs from all categories
        $productIds = $data->flatMap(function ($category) {
            return $category->products->pluck('id');
        })->toArray();

        // Fetch favorites for the authenticated user
        $favorites = [];
        if (auth()->user()) {
            $favorites = MyFavorit::where('user_id', auth()->user()->id)
                ->whereIn('product_id', $productIds)
                ->pluck('product_id')
                ->toArray();
        }

        // Attach `is_favorite` to each product inside each category
        foreach ($data as $category) {
            foreach ($category->products as $product) {
                $product->is_favorite = in_array($product->id, $favorites);
            }
        }

        return $this->success($data, 'Categories with products retrieved successfully', 200);
    }

    public function singleProduct(Request $request, $id)
    {
        $lat = $request->query('lat');
        $lng = $request->query('lng');

        $distance_formula = "(3959 * acos(
            cos(radians({$lat}))
            * cos(radians(shopAddress.latitude))
            * cos(radians(shopAddress.longitude) - radians({$lng}))
            + sin(radians({$lat}))
            * sin(radians(shopAddress.latitude))
        ))";

        $data = Product::with([
            'category',
            'sub_category',
            'images',
            'metaTags',
            'shop:id,user_id,shop_name,shop_image',
            'shop.user:id,first_name,last_name,avatar,role'
        ])
        ->withAvg('reviews', 'rating')
        ->withCount('reviews')
        ->join('shop_addresses AS shopAddress', 'products.shop_info_id', '=', 'shopAddress.shop_info_id')
        ->select('products.*')
        ->selectRaw("{$distance_formula} AS distance_in_miles")
        ->where('products.id', $id)   
        ->first();           

        if (!$data) {
            return $this->error([], 'Product not found', 200);
        }

        // Load address
        $data->load(['shop.address', 'shop.user']);

        if ($data->shop && $data->shop->address) {
            $data->shop->address->distance_in_miles = $data->distance_in_miles;
        }

        // Favorite check
        $data->is_favorite = auth()->check() &&
            MyFavorit::where('user_id', auth()->id())
            ->where('product_id', $id)
            ->exists();

        // Follow check
        $data->shop->is_followed = auth()->check() &&
            FollowShop::where('user_id', auth()->id())
            ->where('shop_info_id', $data->shop_info_id)
            ->exists();

        // More products
        $data->more_products_from_shop = Product::with(['images'])
            ->where('shop_info_id', $data->shop_info_id)
            ->where('id', '!=', $id)
            ->where('status', 'approved')
            ->select('id', 'shop_info_id', 'product_name', 'product_price', 'product_quantity', 'selling_option', 'unlimited_stock', 'out_of_stock')
            ->inRandomOrder()
            ->take(5)
            ->get();

        return $this->success($data, 'Product retrieved successfully', 200);
    }
}
