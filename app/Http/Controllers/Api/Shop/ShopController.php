<?php

namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Models\FollowShop;
use App\Models\MyFavorit;
use App\Models\Product;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    use ApiResponse;

    public function allShops()
    {
        $data = User::with('shopInfo:id,user_id,shop_name,shop_image,shop_banner')
            ->where('role', 'vendor')
            ->where('status', 'active')
            ->whereHas('shopInfo')
            ->select('id', 'first_name', 'last_name', 'role', 'avatar')
            ->get();

        if ($data->isEmpty()) {
            return $this->error([], 'No shops found', 404);
        }

        return $this->success($data, 'All shops retrieved successfully', 200);
    }

    /**
     * Retrieve a list of featured shops.
     *
     * This function fetches all vendors with active status that have their shop marked as featured.
     * It returns a success response with the shop data if found, otherwise an error response.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function featuredShops()
    {
        $data = User::with('shopInfo:id,user_id,shop_name,shop_name,shop_image,shop_banner,is_featured', 'shopInfo.address:id,shop_info_id,address_line_1')->where('role', 'vendor')
            ->select('id', 'first_name', 'last_name', 'role', 'avatar')
            ->whereHas('shopInfo', function ($q) {
                $q->where('is_featured', true);
            })
            ->where('status', 'active')
            ->get();

        if ($data->isEmpty()) {
            return $this->error([], 'No shops found', 404);
        }

        return $this->success($data, 'All featured shops successfully', 200);
    }

    public function shopDetails($id)
    {

        $data = User::with(['shopInfo', 'shopInfo.about', 'shopInfo.policies', 'shopInfo.policies', 'shopInfo.faqs', 'shopInfo.address'])
            ->select('id', 'first_name', 'last_name', 'role', 'avatar')
            ->where('id', $id)
            ->where('role', 'vendor')
            ->where('status', 'active')
            ->first();

        if (auth()->check()) {
            $shopFollow = FollowShop::where('user_id', auth()->id())
                ->where('shop_info_id', $data->shopInfo->id)
                ->exists(); // more efficient
            $data->is_followed = $shopFollow;
        } else {
            $data->is_followed = false;
        }

        if (!$data) {
            return $this->error([], 'Shop not found', 404);
        }

        return $this->success($data, 'Shop details retrieved successfully', 200);
    }

    public function shopFeaturedProducts($id)
    {
        $data = Product::with('images')->where('shop_info_id', $id)
            ->where('is_featured', true)
            ->where('status', 'approved')
            ->select('id', 'shop_info_id', 'product_name', 'product_price', 'is_featured','product_quantity', 'unlimited_stock', 'out_of_stock', 'selling_option')
            ->get();

        if ($data->isEmpty()) {
            return $this->error([], 'No featured products found for this shop', 404);
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

        return $this->success($data, 'Featured products retrieved successfully', 200);
    }

    public function shopProducts(Request $request, $id)
    {
        $item = $request->input('item', 15);
        $query = Product::with(['category', 'sub_category', 'images'])->where('shop_info_id', $id)
            ->where('status', 'approved')
            ->select('id', 'shop_info_id', 'category_id', 'sub_category_id', 'product_name', 'product_price','product_quantity', 'unlimited_stock', 'out_of_stock', 'selling_option');

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // Filter by sub-category
        if ($request->filled('sub_category_id')) {
            $query->where('sub_category_id', $request->input('sub_category_id'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%");
            });
        }

        if ($request->has("short_by")) {
            $shortBy = $request->input("short_by");
            if ($shortBy === "recently_added") {
                $query->latest();
            }
            if ($shortBy === "ascending") {
                $query->orderBy('product_price', 'asc');
            }
            if ($shortBy === "descending") {
                $query->orderBy('product_price', 'desc');
            }
        }

        $data = $query->paginate($item); // Paginate results, 15 per page

        if ($data->isEmpty()) {
            return $this->error([], 'No products found for this shop', 404);
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

        return $this->success($data, 'Products retrieved successfully', 200);
    }
}
