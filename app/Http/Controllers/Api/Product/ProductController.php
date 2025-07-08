<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

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
                        ->select('id', 'category_id', 'sub_category_id', 'shop_info_id', 'product_name', 'product_price','product_quantity', 'unlimited_stock', 'out_of_stock', 'selling_option');
                },
                'products.images'
            ])
            ->get();

        if ($data->isEmpty()) {
            return $this->error([], 'No categories found', 404);
        }

        return $this->success($data, 'Categories with products retrieved successfully', 200);
    }

    public function singleProduct($id)
    {
        $data = Product::with(['category', 'sub_category','images','metaTags','shop.user:id,first_name,last_name,avatar,role','shop:id,user_id,shop_name,shop_image','shop.address'])->find($id);

        if (!$data) {
            return $this->error([], 'Product not found', 404);
        }

        return $this->success($data, 'Product retrieved successfully', 200);
    }
}
