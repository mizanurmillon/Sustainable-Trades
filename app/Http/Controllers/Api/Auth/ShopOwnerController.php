<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class ShopOwnerController extends Controller
{
    use ApiResponse;

    public function shopOwnerRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name'           => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'phone'          => 'required|string|max:15|unique:users,phone',
            'company_name'  => 'required|string|max:255',
            'shop_name'    => 'required|string|max:255',
            'shop_city'   => 'required|string|max:255',
            'avatar'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10240', // 10MB max  
            'shop_banner'  => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10240', // 10MB max
            'shop_about'   => 'nullable|string|max:500',
            'shop_policie' => 'nullable|string|max:500',
            'faqs'         => 'nullable|string|max:500',
            'platforms'    => 'nullable|array',
            'platforms'             => 'nullable|array',
            'platforms.*' => 'string|max:255',
            'urls'         => 'nullable|array',
            'urls.*'       => 'nullable|url|max:255',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city'          => 'required|string|max:255',
            'state'         => 'required|string|max:255',
            'zip_code'      => 'required|string|max:20',
            'display_my_address' => 'boolean',
            'address_10_mile' => 'boolean',
            'do_not_display' => 'boolean',
            'password'       => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'agree_to_terms' => 'required|boolean',
        ], [
            'password.min' => 'The password must be at least 8 characters long.',
            'gender.in'    => 'The selected gender is invalid.',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), $validator->errors()->first(), 422);
        }

        try {
            DB::beginTransaction();

            if ($request->file('avatar')) {
                $avatar                        = $request->file('avatar');
                $avatarName                    = uploadImage($avatar, 'users');
            } else {
                $avatarName = null;
            }

            if ($request->file('shop_banner')) {
                $shopBanner                    = $request->file('shop_banner');
                $shopBannerName                = uploadImage($shopBanner, 'shops');
            } else {
                $shopBannerName = null;
            }

            $user                 = new User();
            $user->first_name     = $request->input('first_name');
            $user->last_name      = $request->input('last_name');
            $user->email          = $request->input('email');
            $user->phone = $request->input('phone');
            $user->company_name = $request->input('company_name');
            $user->password       = Hash::make($request->input('password')); // Hash the password
            $user->agree_to_terms = $request->input('agree_to_terms');
            $user->role = 'vendor'; // Set the role to 'vendor'
            $user->avatar = $avatarName;
            $user->email_verified_at = now(); // Automatically verify email for shop owners
            $user->save();

            $shopInfo = $user->shopInfo()->create([
                'shop_name' => $request->input('shop_name'),
                'shop_city' => $request->input('shop_city'),
                'shop_banner' => $shopBannerName,
                'shop_about' => $request->input('shop_about'),
                'shop_policie' => $request->input('shop_policie'),
                'faqs' => $request->input('faqs'),
            ]);

            // Save platforms and URLs if provided
            if ($request->has('platforms') && is_array($request->platforms) && is_array($request->urls)) {
                foreach ($request->input('platforms') as $index => $platform) {
                    $shopInfo->socialLinks()->create([
                        'platform' => $platform,
                        'url' => $request->urls[$index] ?? null,
                    ]);
                }
            }

            $shopInfo->address()->create([
                'address_line_1' => $request->input('address_line_1'),
                'address_line_2' => $request->input('address_line_2'),
                'latitude' => $request->input('latitude', null),
                'longitude' => $request->input('longitude', null),
                'city' => $request->input('city'),
                'state' => $request->input('state'),
                'postal_code' => $request->input('zip_code'),
                'display_my_address' => $request->input('display_my_address', false),
                'address_10_mile' => $request->input('address_10_mile', false),
                'do_not_display' => $request->input('do_not_display', false),
            ]);

            DB::commit();

            $token = JWTAuth::fromUser($user);
            $user->setAttribute('token', $token);

            return $this->success($user, 'Shop owner registered successfully', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error([], $e->getMessage(), 500);
        }
    }
}
