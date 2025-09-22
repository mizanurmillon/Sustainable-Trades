<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\shopOwnerRegisterRequest;
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

    public function shopOwnerRegister(shopOwnerRegisterRequest $request)
    {
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            if ($request->hasFile('avatar')) {
                $avatar = $request->file('avatar');
                $avatarName = uploadImage($avatar, 'users');
            } else {
                $avatarName = null;
            }

            if ($request->hasFile('shop_image')) {
                $shopImage = $request->file('shop_image');
                $shopImageName = uploadImage($shopImage, 'shops');
            } else {
                $shopImageName = null;
            }

            if ($request->hasFile('shop_banner')) {
                $shopBanner = $request->file('shop_banner');
                $shopBannerName = uploadImage($shopBanner, 'shops');
            } else {
                $shopBannerName = null;
            }

            if ($request->hasFile('about_image')) {
                $aboutImage = $request->file('about_image');
                $aboutImageName = uploadImage($aboutImage, 'shops');
            } else {
                $aboutImageName = null;
            }

            // Find the user by ID
            $user = new User();
            $firstName = $validated['first_name'];
            $lastName = $validated['last_name'];

            // Generate base username
            $baseUsername = strtolower(preg_replace('/[^a-z0-9_]/', '', str_replace(' ', '', $firstName . $lastName)));
            $baseUsername = substr($baseUsername, 0, 20);

            // Ensure username is unique
            $username = $baseUsername;
            $counter = 1;
            while (User::where('username', $username)->exists()) {
                $username = substr($baseUsername, 0, 20 - strlen((string) $counter)) . $counter;
                $counter++;
            }

            $user->first_name = $firstName;
            $user->last_name = $lastName;
            $user->username = $username;
            $user->email = $validated['email'];
            $user->phone = $validated['phone'];
            $user->company_name = $validated['company_name'];
            $user->password = Hash::make($validated['password']); // Hash the password
            $user->agree_to_terms = $validated['agree_to_terms'];
            $user->role = 'vendor'; // Set the role to 'vendor'
            $user->avatar = $avatarName;
            $user->email_verified_at = now(); // Automatically verify email for shop owners
            $user->save();

            $shopInfo = $user->shopInfo()->create([
                'shop_name' => $validated['shop_name'],
                'shop_city' => $validated['shop_city'],
                'shop_banner' => $shopBannerName,
                'shop_image' => $shopImageName,
            ]);

            $shopInfo->about()->create([
                'tagline' => $validated['tagline'],
                'statement' => $validated['statement'],
                'our_story' => $validated['our_story'],
                'about_image' => $aboutImageName,
            ]);

            $shopInfo->policies()->create([
                'shipping_information' => $validated['shipping_information'],
                'return_policy' => $validated['return_policy'],
                'payment_methods' => json_encode($validated['payment_methods']),
            ]);

            if (isset($validated['answers']) && is_array($validated['answers']) && is_array($validated['questions'])) {
                foreach ($validated['answers'] as $index => $answer) {
                    $shopInfo->faqs()->create([
                        'question' => $validated['questions'][$index] ?? '',
                        'answer' => $answer,
                    ]);
                }
            }

            // Save platforms and URLs if provided
            if (isset($validated['platforms']) && is_array($validated['platforms']) && is_array($validated['urls'])) {
                foreach ($validated['platforms'] as $index => $platform) {
                    $shopInfo->socialLinks()->create([
                        'platform' => $platform,
                        'url' => $validated['urls'][$index] ?? null,
                    ]);
                }
            }

            $shopInfo->address()->create([
                'address_line_1' => $validated['address_line_1'],
                'address_line_2' => $validated['address_line_2'],
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'city' => $validated['city'],
                'state' => $validated['state'],
                'postal_code' => $validated['zip_code'],
                'display_my_address' => $validated['display_my_address'] ?? false,
                'address_10_mile' => $validated['address_10_mile'] ?? false,
                'do_not_display' => $validated['do_not_display'] ?? false,
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
