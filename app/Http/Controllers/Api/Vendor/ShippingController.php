<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Models\FlatRate;
use App\Models\WeightRangeRat;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShippingController extends Controller
{
    use ApiResponse;
    
    public function flatRateStore(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'option_name' => 'required|string|max:255',
            'per_order_fee' => 'required|numeric|min:0',
            'per_item_fee' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), $validator->errors()->first(), 422);
        }

        $user = auth()->user();

        if(!$user) {
            return $this->error([], 'User not found', 404);
        }

        $data = FlatRate::updateOrCreate(
        ['shop_id' => $user->shopInfo->id],
            [
                'option_name'   => $request->option_name,
                'per_order_fee' => $request->per_order_fee,
                'per_item_fee'  => $request->per_item_fee,
            ]
        );

        if (!$data) {
            return $this->error([], 'Failed to create flat rate shipping', 500);
        }

        return $this->success($data,'Flat rate shipping created successfully', 200);
    }

    public function weightRangeStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'min_weight' => 'required|numeric|min:0',
            'max_weight' => 'required|numeric|min:0|gt:min_weight',
            'cost' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), $validator->errors()->first(), 422);
        }

        $user = auth()->user();
        if(!$user) {
            return $this->error([], 'User not found', 404);
        }

        $oldData = WeightRangeRat::where('shop_id', $user->shopInfo->id)
            ->where('min_weight', $request->min_weight)
            ->where('max_weight', $request->max_weight)
            ->first();

        if ($oldData) {
            return $this->error([], 'Weight range already exists', 400);
        }

        $data = WeightRangeRat::create([
            'shop_id' => $user->shopInfo->id,
            'min_weight' => $request->min_weight,
            'max_weight' => $request->max_weight,
            'cost' => $request->cost,
        ]);

        if (!$data) {
            return $this->error([], 'Failed to create weight range shipping', 200);
        }

        return $this->success($data,'Weight range shipping created successfully', 200);
           
        
    }

    public function weightRangeList()
    {
        $user = auth()->user();
        if(!$user) {
            return $this->error([], 'User not found', 404);
        }

        $data = WeightRangeRat::where('shop_id', $user->shopInfo->id)->get();

        if ($data->isEmpty()) {
            return $this->error([], 'Failed to fetch weight range shipping', 200);
        }
        return $this->success($data,'Weight range shipping fetched successfully', 200);
    }

    public function weightRangeDelete($id)
    {
        $user = auth()->user();
        if(!$user) {
            return $this->error([], 'User not found', 404);
        }

        $data = WeightRangeRat::find($id);

        if (!$data) {
            return $this->error([], 'Weight range not found',200);
        }

        $data->delete();

        return $this->success([],'Weight range deleted successfully',200);
    }

   
}
