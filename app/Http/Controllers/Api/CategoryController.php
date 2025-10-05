<?php

namespace App\Http\Controllers\Api;

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

    public function singleCategory($id)
    {
        $data = Category::with('products:id,category_id,shop_info_id,product_name,product_price,product_quantity,selling_option')
            ->find($id);

        if (!$data) {
            return $this->error([], 'Category not found', 404);
        }

        // If user is authenticated, fetch favorite products
        $favorites = [];
        if (auth()->check()) {
            $favorites = MyFavorit::where('user_id', auth()->id())
                ->whereIn('product_id', $data->products->pluck('id'))
                ->pluck('product_id')
                ->toArray();
        }

        // Attach `is_favorite` flag to each product
        foreach ($data->products as $product) {
            $product->is_favorite = in_array($product->id, $favorites);
        }

        return $this->success($data, 'Category retrieved successfully', 200);
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
