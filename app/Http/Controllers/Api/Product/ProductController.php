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
            return $this->error([], 'No categories found', 404);
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

    public function singleProduct($id)
    {
        $data = Product::with(['category', 'sub_category', 'images', 'metaTags', 'shop.user:id,first_name,last_name,avatar,role', 'shop:id,user_id,shop_name,shop_image', 'shop.address'])->find($id);

        if (!$data) {
            return $this->error([], 'Product not found', 404);
        }
        // If user is authenticated, fetch favorite products
        if (auth()->user()) {
            $favorite = MyFavorit::where('user_id', auth()->id())
                ->where('product_id', $id)->first(); // Get only the product ID
        }
        if (auth()->user()) {
            $data->is_favorite = $favorite ? true : false;
        } else {
            $data->is_favorite = false;
        }

        // Inject is_followed inside shop object
        if ($data->relationLoaded('shop') && $data->shop && auth()->user()) {
            $data->shop->is_followed = FollowShop::where('user_id', auth()->user()->id)
                ->where('shop_info_id', $data->shop_info_id)
                ->exists();
        } elseif ($data->shop) {
            $data->shop->is_followed = false;
        }

        $moreProducts = Product::with(['images'])
            ->where('shop_info_id', $data->shop_info_id)
            ->where('id', '!=', $id)
            ->where('status', 'approved')
            ->take(5) // Limit to 5 products
            ->get();

        $data->more_products_from_shop = $moreProducts;

        return $this->success($data, 'Product retrieved successfully', 200);
    }
}
