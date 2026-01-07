<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use App\Mail\ArrangeLocalPickupMail;
use App\Models\Cart;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ArrangeLocalPickupController extends Controller
{
    use ApiResponse;

    public function arrangeLocalPickup(Request $request, $id)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'message' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation Error', 422);
        }

        try {
            DB::beginTransaction();
            // Process the arrange local pickup request
            $cart = Cart::with(['CartItems.product'])->where('id', $id)->first();

            if (!$cart) {
                return $this->error([], 'Cart not found', 404);
            }

            // Here you can add logic to save the pickup request to the database or send a notification

            $data = [
                'name' => $request->input('name'),
                'phone' => $request->input('phone'),
                'email' => $request->input('email'),
                'message' => $request->input('message'),
            ];

            // dd( $cart);

            Mail::to($cart->shop->user->email)->send(new ArrangeLocalPickupMail($data, $cart));

            $cart->CartItems()->delete();
            $cart->delete();
            DB::commit();
            return $this->success([], 'Local pickup arranged successfully', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error([], $e->getMessage(), 500);
        }
    }
}
