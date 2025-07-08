<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiResponse;

    public function allCategories()
    {
        $data = Category::with('subcategories')->where('status', 'active')->latest()->get();

        if ($data->isEmpty()) {
            return $this->error([], 'No categories found', 404);
        }

        return $this->success($data, 'Categories retrieved successfully', 200);
    }
}
