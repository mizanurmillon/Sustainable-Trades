<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Models\ShopTax;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShopTaxController extends Controller
{
    use ApiResponse;

    public function store(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'country' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100',
            'is_digital_products' => 'required|boolean',
            'is_shipping' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), $validator->errors()->first(), 422);
        }

        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'User not found', 404);
        }

        $oldData = ShopTax::where('shop_id', $user->shopInfo->id)->where('country', $request->country)->where('state', $request->state)->first();

        if ($oldData) {
            return $this->error([], 'Shop tax already exists for this country and state', 400);
        }

        $data = ShopTax::create([
            'shop_id' => $user->shopInfo->id,
            'country'=> $request->country,
            'state'=> $request->state,
            'rate'=> $request->rate,
            'is_digital_products'=> $request->is_digital_products,
            'is_shipping'=> $request->is_shipping,
        ]);

        if (!$data) {
            return $this->error([], 'Failed to create shop tax', 500);
        }

        return $this->success($data, 'Shop tax created successfully',200);
    }
}
