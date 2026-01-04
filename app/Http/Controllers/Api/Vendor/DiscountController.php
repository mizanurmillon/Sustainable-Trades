<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\DiscountProduct;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DiscountController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'User not found', 404);
        }

        $query = Discount::with('product:id,product_name', 'discountProducts.products:id,product_name')->where('shop_id', $user->shopInfo->id);

        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $data = $query->latest()->get();

        if ($data->isEmpty()) {
            return $this->error([], 'No discounts found', 200);
        }

        return $this->success($data, 'Discounts fetched successfully', 200);
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'discount_type' => 'required|in:automatic_discount,discount_code',
            'promotion_type' => 'required|in:percentage,fixed',
            'amount' => 'required|numeric|min:1',
            'applies' => 'required|in:any_order,single_product',
            'discount_limits' => 'nullable|integer|min:0',
            'start_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',

        ];

        // Role-based validation
        if ($request->discount_type === 'discount_code') {
            $rules['code'] = 'required|string|max:255|unique:discounts,code';
        } else {
            $rules['products_id'] = 'required|array';
            $rules['products_id.*'] = 'exists:products,id';
        }

        if ($request->has('applies') && $request->applies === 'single_product') {
            $rules['product_id'] = 'required|exists:products,id';
        } else {
            $rules['product_id'] = 'nullable';
        }

        if ($request->never_expires) {
            $rules['end_date'] = 'nullable|date';
            $rules['end_time'] = 'nullable|date_format:H:i';
        } else {
            $rules['end_date'] = 'required|date|after_or_equal:start_date';
            $rules['end_time'] = 'required|date_format:H:i';
        }

        // Validate request
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->error($validator->errors(), $validator->errors()->first(), 422);
        }

        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'User not found', 404);
        }

        $data = Discount::create([
            'shop_id' => $user->shopInfo->id,
            'name' => $request->name,
            'discount_type' => $request->discount_type,
            'code' => $request->code ?? null,
            'promotion_type' => $request->promotion_type,
            'amount' => $request->amount,
            'applies' => $request->applies,
            'product_id' => $request->product_id ?? null,
            'discount_limits' => $request->discount_limits ?? 0,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date ?? null,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time ?? null,
            'never_expires' => $request->never_expires ?? false,
        ]);

        if ($request->discount_type == 'automatic_discount') {
            if ($request->has('products_id')) {
                foreach ($request->products_id as $productId) {
                    DiscountProduct::create([
                        'discount_id' => $data->id,
                        'product_id' => $productId,
                    ]);
                }
            }
        }

        if (!$data) {
            return $this->error([], 'Failed to create discount', 500);
        }

        $data->load('discountProducts');

        return $this->success($data, 'Discount created successfully', 200);
    }

    public function discount($id)
    {
        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'User not found', 404);
        }

        $data = Discount::with('product:id,product_name', 'discountProducts.products:id,product_name')->where('shop_id', $user->shopInfo->id)->find($id);

        if (!$data) {
            return $this->error([], 'Discount not found', 404);
        }

        return $this->success($data, 'Discount fetched successfully', 200);
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'discount_type' => 'required|in:automatic_discount,discount_code',
            'promotion_type' => 'required|in:percentage,fixed',
            'amount' => 'required|numeric|min:1',
            'applies' => 'required|in:any_order,single_product',
            'discount_limits' => 'nullable|integer|min:0',
            'start_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',

        ];

        // Role-based validation
        if ($request->discount_type === 'discount_code') {
            $rules['code'] = 'required|string|max:255|unique:discounts,code';
        } else {
            $rules['products_id'] = 'required|array';
            $rules['products_id.*'] = 'exists:products,id';
        }

        if ($request->has('applies') && $request->applies === 'single_product') {
            $rules['product_id'] = 'required|exists:products,id';
        } else {
            $rules['product_id'] = 'nullable';
        }

        if ($request->never_expires) {
            $rules['end_date'] = 'nullable|date';
            $rules['end_time'] = 'nullable|date_format:H:i';
        } else {
            $rules['end_date'] = 'required|date|after_or_equal:start_date';
            $rules['end_time'] = 'required|date_format:H:i';
        }

        // Validate request
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->error($validator->errors(), $validator->errors()->first(), 422);
        }

        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'User not found', 404);
        }

        $data = Discount::where('shop_id', $user->shopInfo->id)->find($id);

        if (!$data) {
            return $this->error([], 'Discount not found', 404);
        }

        $data->update([
            'name' => $request->name,
            'discount_type' => $request->discount_type,
            'code' => $request->code ?? null,
            'promotion_type' => $request->promotion_type,
            'amount' => $request->amount,
            'applies' => $request->applies,
            'product_id' => $request->product_id ?? null,
            'discount_limits' => $request->discount_limits ?? 0,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date ?? null,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time ?? null,
            'never_expires' => $request->never_expires ?? false,
        ]);

        if ($request->discount_type == 'automatic_discount') {
            if ($request->has('products_id')) {
                $data->discountProducts()->delete();
                foreach ($request->products_id as $productId) {
                    DiscountProduct::create([
                        'discount_id' => $data->id,
                        'product_id' => $productId,
                    ]);
                }
            }
        }

        return $this->success($data, 'Discount updated successfully', 200);
    }

    public function bulkDelete(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'User not found', 404);
        }

        // Validate request
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:discounts,id',
        ]);

        $ids = $request->ids;

        // Delete only the discounts that belong to this user's shop
        $deletedCount = Discount::where('shop_id', $user->shopInfo->id)
            ->whereIn('id', $ids)
            ->delete();

        return $this->success(['deleted' => $deletedCount], 'Discounts deleted successfully', 200);
    }

    public function status(Request $request, $id)
    {
        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'User not found', 404);
        }

        $data = Discount::where('shop_id', $user->shopInfo->id)->find($id);

        if (!$data) {
            return $this->error([], 'Discount not found', 404);
        }

        $data->status = $request->status;
        $data->save();

        return $this->success($data, 'Discount status updated successfully', 200);
    }
}
