<?php

namespace App\Http\Controllers\Api;

use App\Models\Review;
use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Notifications\ReviewNotification;

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

        if (!$user) {
            return $this->error([], 'User not found', 404);
        }

        $product = Product::find($id);

        if (!$product) {
            return $this->error([], 'Product not found', 404);
        }

        try {

            $review = Review::create([
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
                    $review->images()->create([
                        'image' => $imageName,
                    ]);
                }
            }

            if (!$review) {
                return $this->error([], 'Failed to add review', 500);
            }

            // Notify shop owner

            $review->shop->user->notify(new ReviewNotification(
                subject: 'New review added',
                message: 'A new review has been added for ' . $review->product->product_name,
                type: 'success',
                review: $review
            ));

            $review->load('images');

            return $this->success($review, 'Review added successfully', 200);
        } catch (\Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }

    public function myReviews()
    {
        $user = auth()->user();

        $reviews = Review::with('product:id,product_name', 'product.images', 'images')
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        if ($reviews->isEmpty()) {
            return $this->error([], 'No reviews found', 200);
        }

        return $this->success($reviews, 'My reviews retrieved successfully', 200);
    }

    public function shopReviews($id)
    {
        $reviews = Review::with('user:id,first_name,last_name,avatar', 'images', 'product:id,product_name', 'product.images')
            ->where('shop_info_id', $id)
            ->latest()
            ->paginate(5);

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
            ->paginate(5);

        if ($reviews->isEmpty()) {
            return $this->error([], 'No reviews found for this product', 200);
        }

        return $this->success($reviews, 'Product reviews retrieved successfully', 200);
    }
}
