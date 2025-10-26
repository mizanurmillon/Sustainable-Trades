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
        $radius = $request->query('radius', 5); // default 5 km

        if (!$lat || !$lng) {
            return $this->error([], 'Latitude and longitude are required', 400);
        }

        // Category exists check
        $category = Category::find($id);
        if (!$category) {
            return $this->error([], 'Category not found', 404);
        }

        // Get products of this category based on nearby shop location
        $products = Product::with([
            'images',
            'category:id,name',
            'shop:id,shop_name',
            'shop.address:id,shop_info_id,latitude,longitude,address_line_1,city'
        ])
            ->where('category_id', $id)
            ->where('status', 'approved')
            ->whereHas('shop.address', function ($query) use ($lat, $lng, $radius) {
                $query->whereRaw("
                6371 * acos(
                    cos(radians(?)) * cos(radians(latitude)) *
                    cos(radians(longitude) - radians(?)) +
                    sin(radians(?)) * sin(radians(latitude))
                ) <= ?
            ", [$lat, $lng, $lat, $radius]);
            });
        
        $products = $products->get(['id', 'category_id', 'shop_info_id', 'product_name', 'product_price', 'product_quantity', 'selling_option']);

        if ($products->isEmpty()) {
            return $this->error([], 'No nearby products found in this category', 404);
        }

        // If user logged in, check favorites
        $favorites = [];
        if (auth()->check()) {
            $favorites = MyFavorit::where('user_id', auth()->id())
                ->whereIn('product_id', $products->pluck('id'))
                ->pluck('product_id')
                ->toArray();
        }

        foreach ($products as $product) {
            $product->is_favorite = in_array($product->id, $favorites);
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
