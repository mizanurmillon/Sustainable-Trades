<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    use ApiResponse;

    public function addReview(Request $request, $id)
    {
        $validator = validator()->make($request->all(), [
            'title' => 'required|string|max:255',
            'rating' => 'required|numeric|min:1|max:5',
            'message' => 'required|string|max:5000',
            'images' => 'array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:20480',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = auth()->user();

        if(!$user) {
            return $this->error([], 'User not found', 404);
        }

        $product = Product::find($id);

        if (!$product) {
            return $this->error([], 'Product not found', 404);
        }

        try{
           
            $data = Review::create([
                'title' => $request->title,
                'rating' => $request->rating,
                'message' => $request->message,
                'product_id' => $product->id,
                'user_id' => $user->id,
                'shop_info_id' => $product->shop_info_id
            ]);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imageName = uploadImage($image, 'reviews');
                    $data->images()->create([
                        'image' => $imageName,
                    ]);
                }
            }

            if (!$data) {
                return $this->error([], 'Failed to add review', 500);
            }

            $data->load('images');

            return $this->success($data, 'Review added successfully', 200);

        }catch(\Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
        
    }

    public function shopReviews($id)
    {
        $reviews = Review::with('user:id,first_name,last_name,avatar', 'images', 'product:id,product_name', 'product.images')
            ->where('shop_info_id', $id)
            ->latest()
            ->paginate(10);

        if ($reviews->isEmpty()) {
            return $this->error([], 'No reviews found for this shop', 200);
        }

        return $this->success($reviews, 'Shop reviews retrieved successfully', 200);
    }

    public function productReviews($id)
    {
        $reviews = Review::with('user:id,first_name,last_name,avatar', 'images')
            ->where('product_id', $id)
            ->latest()
            ->paginate(10);

        if ($reviews->isEmpty()) {
            return $this->error([], 'No reviews found for this product', 200);
        }

        return $this->success($reviews, 'Product reviews retrieved successfully', 200);
    }

    
}
