<?php

namespace App\Http\Controllers\Api\Cart;

use App\Models\Cart;
use App\Models\Product;
use App\Models\CartItem;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    use ApiResponse;

    public function addToCart(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), $validator->errors()->first(), 422);
        }

        $user = auth()->user();
        if (!$user) {
            return $this->error([], 'Unauthorized', 401);
        }

        $product = Product::find($id);

        if (!$product) {
            return $this->error([], 'Product not found', 404);
        }

        $cart = Cart::firstOrCreate([
            'user_id' => $user->id,
            'shop_id' => $product->shop_info_id,
        ]);

        $cartItem = $cart->CartItems()->where('product_id', $product->id)->first();

        if ($cartItem) {
            $cartItem->quantity += $request->quantity;
            $cartItem->save();
        } else {
            $cart->CartItems()->create([
                'product_id' => $product->id,
                'quantity' => $request->quantity,
            ]);
        }

        if (!$cart) {
            return $this->error([], 'Failed to add to cart', 0);
        }

        $cart->load('CartItems');

        return $this->success($cart, 'Product added to cart successfully', 200);
    }

    public function updateCart(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), $validator->errors()->first(), 422);
        }

        $user = auth()->user();
        if (!$user) {
            return $this->error([], 'Unauthorized', 401);
        }

        $cartItem = CartItem::find($id);

        if (!$cartItem) {
            return $this->error([], 'Cart item not found', 404);
        }

        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return $this->success($cartItem, 'Cart item updated successfully', 200);
    }

    public function getCart()
    {
        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'Unauthorized', 401);
        }

        $carts = Cart::where('user_id', $user->id)
            ->with([
                'shop:id,user_id,shop_name,shop_image',
                'shop.address',
                'CartItems.product:id,product_name,product_price,product_quantity,fulfillment',
                'CartItems.product.images'
            ])
            ->get();

        if ($carts->isEmpty()) {
            return $this->error([], 'Cart is empty', 404);
        }

        // 2. Initialize tracking variables
        $hasShipping = false;
        $hasLocalPickup = false;
        $totalCartItems = 0;

        // 3. Loop through carts and items to determine fulfillment and count
        foreach ($carts as $cart) {
            foreach ($cart->CartItems as $item) {
                $totalCartItems++;
                $fulfillment = $item->product->fulfillment ?? '';

                if (str_contains($fulfillment, 'Shipping')) {
                    $hasShipping = true;
                }
                if (str_contains($fulfillment, 'Local Pickup')) {
                    $hasLocalPickup = true;
                }
            }
        }

        // 4. Determine final fulfillment type logic
        if ($hasShipping && $hasLocalPickup) {
            $fulfillment_type = "Both";
        } elseif ($hasShipping) {
            $fulfillment_type = "Shipping";
        } elseif ($hasLocalPickup) {
            $fulfillment_type = "Arrange Local Pickup";
        } else {
            $fulfillment_type = "Not Specified";
        }

        $totalCartItems = $carts->sum(function ($c) {
            return $c->CartItems->count();
        });

        return $this->success(
            [
                'total_cart_items' => $totalCartItems,
                'cart' => $carts,
                'fulfillment_type' => $fulfillment_type
            ],
            'Cart retrieved successfully',
            200
        );
    }


    public function deleteCartItem($id)
    {
        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'Unauthorized', 401);
        }

        $cartItem = CartItem::where('id', $id)->first();

        if (!$cartItem) {
            return $this->error([], 'Cart item not found', 404);
        }

        $cartItem->delete();

        return $this->success([], 'Cart item removed successfully', 200);
    }

    public function emptyCart()
    {
        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'Unauthorized', 401);
        }

        $carts = Cart::where('user_id', $user->id)->get();

        if ($carts->isEmpty()) {
            return $this->error([], 'Cart is already empty', 404);
        }

        foreach ($carts as $cart) {
            $cart->CartItems()->delete(); // delete cart_items first
            $cart->delete(); // delete cart row
        }

        return $this->success([], 'Cart emptied successfully', 200);
    }

    public function deleteCart($id)
    {
        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'Unauthorized', 401);
        }

        $cart = Cart::where('id', $id)->where('user_id', $user->id)->first();

        if (!$cart) {
            return $this->error([], 'Cart not found', 404);
        }

        $cart->delete(); // delete cart row

        return $this->success([], 'Cart deleted successfully', 200);
    }
}
