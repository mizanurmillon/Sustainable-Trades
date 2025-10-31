<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\Category;
use App\Models\MyFavorit;
use App\Models\SubCategory;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    use ApiResponse;

    public function categories()
    {
        $data = Category::where('status', 'active')->latest()->get();

        if ($data->isEmpty()) {
            return $this->error([], 'No categories found', 404);
        }

        return $this->success($data, 'Categories retrieved successfully', 200);
    }

    public function singleCategory(Request $request, $id)
    {
        $lat = $request->query('lat');
        $lng = $request->query('lng');
        $radius = $request->query('radius', 5); // default 5 miles

        if (!$lat || !$lng) {
            return $this->error([], 'Latitude and longitude are required', 400);
        }

        // Check if category exists
        $category = Category::find($id);
        if (!$category) {
            return $this->error([], 'Category not found', 404);
        }

        // Get products of this category based on nearby shop location
        $products = Product::query()
            ->join('shop_infos', 'products.shop_info_id', '=', 'shop_infos.id')
            ->join('shop_addresses', 'shop_infos.id', '=', 'shop_addresses.shop_info_id')
            ->where('products.category_id', $id)
            ->where('products.status', 'approved')
            ->select(
                'products.id',
                'products.category_id',
                'products.shop_info_id',
                'products.product_name',
                'products.product_price',
                'products.product_quantity',
                'products.selling_option',
                'products.unlimited_stock',
                'products.out_of_stock'
            )
            ->selectRaw("
            (3959 * acos(
                cos(radians(?)) * cos(radians(shop_addresses.latitude)) *
                cos(radians(shop_addresses.longitude) - radians(?)) +
                sin(radians(?)) * sin(radians(shop_addresses.latitude))
            )) AS distance
        ", [$lat, $lng, $lat])
            ->orderBy('distance', 'ASC')
            ->with([
                'images',
                'category:id,name',
                'shop:id,shop_name',
                'shop.address:id,shop_info_id,latitude,longitude,address_line_1,city'
            ])
            ->get();

        if ($products->isEmpty()) {
            return $this->error([], 'No nearby products found in this category', 404);
        }

        // If user logged in, mark favorites
        $favorites = [];
        if (auth()->check()) {
            $favorites = MyFavorit::where('user_id', auth()->id())
                ->whereIn('product_id', $products->pluck('id'))
                ->pluck('product_id')
                ->toArray();
        }

        foreach ($products as $product) {
            $product->is_favorite = in_array($product->id, $favorites);
            $product->distance = round($product->distance, 2); // distance in km, 2 decimal
        }

        $response = [
            'category' => $category,
            'products' => $products
        ];

        return $this->success($response, 'Category-wise nearby products retrieved successfully', 200);
    }




    /**
     * Retrieves categories with their subcategories.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function categoryAndSubCategories()
    {
        $data = Category::with('subcategories')->where('status', 'active')->latest()->get();

        if ($data->isEmpty()) {
            return $this->error([], 'No categories found', 404);
        }

        return $this->success($data, 'Categories retrieved successfully', 200);
    }

    public function subCategories()
    {
        $data = SubCategory::where('status', 'active')->latest()->get();

        if ($data->isEmpty()) {
            return $this->error([], 'No subcategories found', 404);
        }

        return $this->success($data, 'Subcategories retrieved successfully', 200);
    }
}
