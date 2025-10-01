<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    use ApiResponse;
    
    public function productStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string|max:255',
            'product_price' => 'required|numeric|min:0',
            'product_quantity' => 'nullable|numeric|min:1',
            'weight'=> 'nullable|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'unlimited_stock' => 'nullable|boolean',
            'out_of_stock' => 'nullable|boolean',
            'video' => 'nullable|file|mimes:mp4,mov,avi,flv|max:20480', // 20MB max
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'fulfillment' => 'required|string|max:255',
            'selling_option' => 'required|string|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'product_image' => 'required|array',
            'product_image.*' => 'file|mimes:jpg,jpeg,png,gif|max:20480', // 20MB max per image
            'is_featured' => 'nullable|boolean',
        ]);

        // dd($request->all());

        if ($validator->fails()) {
            return $this->error($validator->errors(),$validator->errors()->first(), 422);
        }

        $user = auth()->user();

        if(!$user)
        {
            return $this->error([],'User Not Found',400);
        }

        try{
            DB::beginTransaction();

            if($request->has('video')){
                $video                        = $request->file('video');
                $videoName                    = uploadImage($video, 'products/videos');
            }else{
                $videoName = null; // Default to null if no video is uploaded
            }
            
            $data = Product::create([
                'shop_info_id' => $user->shopInfo->id,
                'product_name' => $request->product_name,
                'product_price' => $request->product_price,
                'product_quantity' => $request->product_quantity,
                'weight' => $request->weight,
                'cost' => $request->cost,
                'unlimited_stock' => $request->unlimited_stock ?? false,
                'out_of_stock' => $request->out_of_stock ?? false,
                'video' => $videoName,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'sub_category_id' => $request->sub_category_id,
                'fulfillment' => $request->fulfillment,
                'selling_option' => $request->selling_option,
                'is_featured' => $request->is_featured ?? false,
                'status' => 'listing',
            ]);

            if ($request->has('tags')) {
                foreach ($request->tags as $tag) {
                    $data->metaTags()->create([
                        'tag' => $tag
                    ]);
                }
            }

            if ($request->hasFile('product_image')) {
                foreach ($request->file('product_image') as $image) {
                    $imageName = uploadImage($image, 'products');
                    $data->images()->create([
                        'image' => $imageName,
                    ]);
                }
            }

            DB::commit();

            $data->load(['images', 'metaTags']);

            return $this->success($data,'Product created successfully',201);

        }catch (\Exception $e) {
            DB::rollBack();
            return $this->error([],$e->getMessage(),500);
        }
    }

    public function productList(Request $request)
    {
        $user = auth()->user();

        if(!$user)
        {
            return $this->error([],'User Not Found',400);
        }

        $products = Product::where('shop_info_id', $user->shopInfo->id)
            ->with(['images'])
            ->select([
                'id',
                'product_name',
                'product_price',
                'product_quantity',
                'status',
            ]);

        if ($request->has('status')) {
            $products->where('status', $request->status);
        }

        if($request->has('short_by') == 'a-z')
        {
            $products->orderBy('product_name', 'asc');
        }
        elseif($request->has('short_by') == 'z-a')
        {
            $products->where('product_name', 'desc');
        }

        $data = $products->get();

        if ($data->isEmpty()) {
            return $this->error([],'No products found',404);

        }

        return $this->success($data, 'Products retrieved successfully', 200);
    }

    public function productDetails($id)
    {
        $user = auth()->user();

        if(!$user)
        {
            return $this->error([],'User Not Found',400);
        }

        $product = Product::where('shop_info_id', $user->shopInfo->id)
            ->with(['images', 'metaTags'])
            ->find($id);

        if (!$product) {
            return $this->error([],'Product not found',404);
        }

        return $this->success($product, 'Product details retrieved successfully', 200);
    }

    public function productRequestApproval($id)
    {
        $user = auth()->user();

        if(!$user)
        {
            return $this->error([],'User Not Found',400);
        }

        $product = Product::where('shop_info_id', $user->shopInfo->id)
            ->find($id);

        if (!$product) {
            return $this->error([],'Product not found',404);
        }
       
        $product->status = 'pending';
        $product->save();

        return $this->success($product, 'Product approval requested successfully', 200);
    }

    public function productUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'sometimes|required|string|max:255',
            'product_price' => 'sometimes|required|numeric|min:0',
            'product_quantity' => 'nullable|numeric|min:1',
            'weight'=> 'nullable|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'unlimited_stock' => 'nullable|boolean',
            'out_of_stock' => 'nullable|boolean',
            'video' => 'nullable|file|mimes:mp4,mov,avi,flv|max:20480', // 20MB max
            'description' => 'sometimes|required|string',
            'category_id' => 'sometimes|required|exists:categories,id',
            'sub_category_id' => 'sometimes|required|exists:sub_categories,id',
            'fulfillment' => 'sometimes|required|string|max:255',
            'selling_option' => 'sometimes|required|string|max:255',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'product_image' => 'nullable|array',
            'product_image.*' => 'file|mimes:jpg,jpeg,png,gif|max:20480', // 20MB max per image
            'is_featured' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(),$validator->errors()->first(), 422);
        }

        $user = auth()->user();

        if(!$user)
        {
            return $this->error([],'User Not Found',400);
        }

        try{
            DB::beginTransaction();

            $product = Product::where('shop_info_id', $user->shopInfo->id)
                ->find($id);

            if (!$product) {
                return $this->error([],'Product not found',404);
            }

            if ($request->has('video')) {
                // Delete the old video if it exists
                if ($product->video && file_exists(public_path($product->video))) {
                    unlink(public_path($product->video));
                }
                $video = $request->file('video');
                $videoName = uploadImage($video, 'products/videos');
            } else {
                $videoName = $product->video; // Keep the old video if no new one is uploaded
            }

            $product->update([
                'product_name' => $request->product_name ?? $product->product_name,
                'product_price' => $request->product_price ?? $product->product_price,
                'product_quantity' => $request->product_quantity ?? $product->product_quantity,
                'weight' => $request->weight,
                'cost' => $request->cost,
                'unlimited_stock' => $request->unlimited_stock ?? $product->unlimited_stock,
                'out_of_stock' => $request->out_of_stock ?? $product->out_of_stock,
                'video'=> $videoName,
                'description' => $request->description,
                'category_id' => $request->category_id,
                'sub_category_id' => $request->sub_category_id,
                'fulfillment' => $request->fulfillment,
                'selling_option' => $request->selling_option,
                'is_featured' => $request->is_featured,
            ]);

            if ($request->has('tags')) {
                // Delete old tags
                $product->metaTags()->delete();
                // Add new tags
                foreach ($request->tags as $tag) {
                    $product->metaTags()->create([
                        'tag' => $tag
                    ]);
                }
            }

            if ($request->has('product_image')) {
                $product->images()->delete();
                foreach ($request->product_image as $image) {
                    $imageName = uploadImage($image, 'products');
                    $product->images()->create([
                        'image' => $imageName,
                    ]);
                }
            }
            
            DB::commit();
            $product->load(['images', 'metaTags']);
            return $this->success($product, 'Product updated successfully', 200);


        }catch(\Exception $e){
            DB::rollBack();
            return $this->error([],$e->getMessage(),500);
        }
    }

    public function productDelete($id)
    {
        $user = auth()->user();

        if(!$user)
        {
            return $this->error([],'User Not Found',400);
        }

        $product = Product::where('shop_info_id', $user->shopInfo->id)
            ->find($id);

        if (!$product) {
            return $this->error([],'Product not found',404);
        }

        try {
            DB::beginTransaction();

            // Delete product images
            foreach ($product->images as $image) {
                if (file_exists(public_path($image->image))) {
                    unlink(public_path($image->image));
                }
                $image->delete();
            }

            // Delete product video
            if ($product->video && file_exists(public_path($product->video))) {
                unlink(public_path($product->video));
            }

            // Delete product meta tags
            $product->metaTags()->delete();

            // Delete the product
            $product->delete();

            DB::commit();
            return $this->success([],'Product deleted successfully',200);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error([],$e->getMessage(),500);
        }
    }
}


