<?php

namespace App\Http\Controllers\Api\Shop;

use App\Models\User;
use App\Models\Product;
use App\Models\MyFavorit;
use App\Models\FollowShop;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ShopController extends Controller
{
    use ApiResponse;

    public function allShops(Request $request)
    {
        $query = User::with('shopInfo:id,user_id,shop_name,shop_image,shop_banner', 'shopInfo.address')
            ->where('role', 'vendor')
            ->whereHas('membership')
            ->where('status', 'active')
            ->whereHas('shopInfo')
            ->select('id', 'first_name', 'last_name', 'role', 'avatar');

        if ($request->has('address')) {
            $address = $request->input('address');

            $query->whereHas('shopInfo.address', function ($q) use ($address) {
                $q->where(function ($subQuery) use ($address) {
                    $subQuery->where('address_line_1', 'like', "%{$address}%")
                        ->orWhere('address_line_2', 'like', "%{$address}%")
                        ->orWhere('city', 'like', "%{$address}%")
                        ->orWhere('state', 'like', "%{$address}%")
                        ->orWhere('postal_code', 'like', "%{$address}%");
                });
            });
        }


        $data = $query->get();

        if ($data->isEmpty()) {
            return $this->error([], 'No shops found', 200);
        }

        // ✅ Manually calculate avg_rating and total_reviews for each shop
        $data->transform(function ($user) {
            if ($user->shopInfo) {
                $avg = $user->shopInfo->reviews()->avg('rating');

                $user->shopInfo->avg_rating = round($avg ?? 0, 1);
            }
            return $user;
        });


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

    public function featuredShops(Request $request)
    {
        $lat = $request->query('lat');
        $lng = $request->query('lng');
        $radius = $request->query('radius', 5); // default 5 miles

        if (!$lat || !$lng) {
            return $this->error([], 'Latitude and longitude are required', 400);
        }

        $data = User::query()
            ->with([
                'shopInfo:id,user_id,shop_name,shop_image,shop_banner,is_featured,shop_city',
                'shopInfo.address:id,shop_info_id,latitude,longitude,address_line_1,address_line_2,city,state,postal_code',
            ])
            ->where('role', 'vendor')
            ->where('status', 'active')
            ->whereHas('shopInfo', function ($q) {
                $q->where('is_featured', true);
            })
            ->join('shop_infos', 'users.id', '=', 'shop_infos.user_id')
            ->join('shop_addresses', 'shop_infos.id', '=', 'shop_addresses.shop_info_id')
            ->select('users.id', 'users.first_name', 'users.last_name', 'users.role', 'users.avatar')
            ->selectRaw("
            (3959 * acos(
                cos(radians(?)) * cos(radians(shop_addresses.latitude)) *
                cos(radians(shop_addresses.longitude) - radians(?)) +
                sin(radians(?)) * sin(radians(shop_addresses.latitude))
            )) AS distance
        ", [$lat, $lng, $lat])
            ->having('distance', '<=', $radius)
            ->orderBy('distance', 'ASC')
            ->get();

        if ($data->isEmpty()) {
            return $this->error([], 'No nearby featured shops found', 200);
        }

        // ✅ Manually calculate avg_rating and total_reviews for each shop
        $data->transform(function ($user) {
            if ($user->shopInfo) {
                $avg = $user->shopInfo->reviews()->avg('rating');
                $user->shopInfo->avg_rating = round($avg ?? 0, 1);
            }
            return $user;
        });

        return $this->success($data, 'Nearby featured shops retrieved successfully', 200);
    }

    public function shopDetails($id)
    {

        $data = User::with([
            'shopInfo',
            'shopInfo.about',
            'shopInfo.policies',
            'shopInfo.faqs',
            'shopInfo.address',
            'shopInfo.socialLinks',
        ])
            ->select('id', 'first_name', 'last_name', 'phone', 'email', 'role', 'avatar', 'company_name')
            ->where('id', $id)
            ->where('role', 'vendor')
            ->where('status', 'active')
            ->first();
        if ($data && $data->shopInfo) {
            $data->rating_avg = $data->shopInfo->reviews()->avg('rating');
        }
        if (auth()->check()) {
            $shopFollow = FollowShop::where('user_id', auth()->id())
                ->where('shop_info_id', $data->shopInfo->id)
                ->exists(); // more efficient
            $data->is_followed = $shopFollow;
        } else {
            $data->is_followed = false;
        }

        if (!$data) {
            return $this->error([], 'Shop not found', 200);
        }

        return $this->success($data, 'Shop details retrieved successfully', 200);
    }

    public function shopFeaturedProducts($id)
    {
        $data = Product::with('images')->where('shop_info_id', $id)
            ->where('is_featured', true)
            ->where('status', 'approved')
            ->select('id', 'shop_info_id', 'product_name', 'product_price', 'is_featured', 'product_quantity', 'unlimited_stock', 'out_of_stock', 'selling_option')
            ->get();

        if ($data->isEmpty()) {
            return $this->error([], 'No featured products found for this shop', 200);
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
            ->select('id', 'shop_info_id', 'category_id', 'sub_category_id', 'product_name', 'product_price', 'product_quantity', 'unlimited_stock', 'out_of_stock', 'selling_option');

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
            return $this->error([], 'No products found for this shop', 200);
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
