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
        
        $query = Product::with('images', 'shop:id,user_id,shop_name','shop.address')->where('status', 'approved')->select('id','shop_info_id','product_name', 'product_price', 'product_quantity', 'unlimited_stock', 'out_of_stock', 'selling_option')->withAvg('reviews', 'rating');

        if($request->has('search')) {
            $query->where('product_name', 'like', '%' . $request->search . '%');
        }

        $data = $query->latest()->get();

        if ($data->isEmpty()) {
            return $this->error([], 'No products found', 200);
        }

        return $this->success($data,'Data fetched successfully',200);
    }

    public function isFeaturedProduct()
    {
        $data = Product::with('images')->where('is_featured', true)->where('status', 'approved')->withAvg('reviews', 'rating')->latest()->get();

        if ($data->isEmpty()) {
            return $this->error([], 'No featured products found', 200);
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

    /**
     * Retrieves nearby products based on the provided address.
     * 
     * If no address is provided, it will return the latest 10 products.
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function nearbyProduct(Request $request)
    {
        $lat = $request->input('lat');
        $lng = $request->input('lng');
        $radius = $request->input('radius', 5); // Default radius is 5 miles

        if (!$lat || !$lng) {
            return $this->error([], 'Latitude and longitude are required', 400);
        }

        $radius = $radius * 1; // Convert to numeric

        $query = Product::with('images', 'shop')
            ->join('shop_addresses', 'products.shop_info_id', '=', 'shop_addresses.shop_info_id')
            ->selectRaw(
                'products.id, products.shop_info_id, products.product_name, products.product_price, products.product_quantity, products.selling_option,
            (3959 * acos(
                cos(radians(?)) * cos(radians(shop_addresses.latitude)) *
                cos(radians(shop_addresses.longitude) - radians(?)) +
                sin(radians(?)) * sin(radians(shop_addresses.latitude))
            )) AS distance',
                [$lat, $lng, $lat]
            )
            ->where('products.status', 'approved');

        // Optional address filter
        if ($request->has('address')) {
            $address = $request->input('address');
            $query->where(function ($q) use ($address) {
                $q->where('shop_addresses.address_line_1', 'like', "%{$address}%")
                    ->orWhere('shop_addresses.address_line_2', 'like', "%{$address}%")
                    ->orWhere('shop_addresses.city', 'like', "%{$address}%")
                    ->orWhere('shop_addresses.state', 'like', "%{$address}%")
                    ->orWhere('shop_addresses.postal_code', 'like', "%{$address}%");
            });
        }

        $data = $query->having('distance', '<=', $radius)
            ->orderBy('distance', 'ASC')
            ->withAvg('reviews', 'rating')
            ->limit(10)
            ->get();

        if ($data->isEmpty()) {
            return $this->error([], 'No nearby products found', 200);
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


        return $this->success($data, 'Nearby products retrieved successfully', 200);
    }
}
