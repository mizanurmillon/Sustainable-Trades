<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\shopOwnerUpdateRequest;
use App\Http\Requests\shopOwnerRegisterRequest;

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
                'payment_methods' => ($validated['payment_methods']),
            ]);

            if (isset($validated['answers']) && is_array($validated['answers']) && is_array($validated['questions'])) {
                foreach ($validated['answers'] as $index => $answer) {
                    $shopInfo->faqs()->create([
                        'question' => $validated['questions'][$index] ?? '',
                        'answer' => $answer,
                    ]);
                }
            }

            // Save Social Links
            $shopInfo->socialLinks()->create([
                'website_url' => $validated['website_url'] ?? null,
                'facebook_url' => $validated['facebook_url'] ?? null,
                'instagram_url' => $validated['instagram_url'] ?? null,
                'pinterest_url' => $validated['pinterest_url'] ?? null,
            ]);

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

            $user->load('shopInfo.address', 'shopInfo.socialLinks', 'shopInfo.about', 'shopInfo.policies', 'shopInfo.faqs');

            return $this->success($user, 'Shop owner registered successfully', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error([], $e->getMessage(), 500);
        }
    }


    public function shopOwnerDataUpdate(shopOwnerUpdateRequest $request)
    {
        // dd($request->all());

        $user = auth()->user();

        if (!$user) {
            return $this->error([], 'User Not Found', 400);
        }

        $validated = $request->validated();

        DB::beginTransaction();
        try {
            if ($request->hasFile('avatar')) {
                $oldPath = public_path($user->avatar);
                if (file_exists($oldPath) && is_file($oldPath)) {
                    unlink($oldPath);
                }
                $avatar = $request->file('avatar');
                $avatarName = uploadImage($avatar, 'users');
            } else {
                $avatarName = $user->avatar;
            }

            if ($request->hasFile('shop_image')) {
                $oldPath = public_path($user->shopInfo->shop_image);
                if (file_exists($oldPath) && is_file($oldPath)) {
                    unlink($oldPath);
                }
                $shopImage = $request->file('shop_image');
                $shopImageName = uploadImage($shopImage, 'shops');
            } else {
                $shopImageName = $user->shopInfo->shop_image;
            }

            if ($request->hasFile('shop_banner')) {
                $oldPath = public_path($user->shopInfo->shop_banner);
                if (file_exists($oldPath) && is_file($oldPath)) {
                    unlink($oldPath);
                }
                $shopBanner = $request->file('shop_banner');
                $shopBannerName = uploadImage($shopBanner, 'shops');
            } else {
                $shopBannerName = $user->shopInfo->shop_banner;
            }

            if ($request->hasFile('about_image')) {
                $oldPath = public_path($user->shopInfo->about->about_image);
                if (file_exists($oldPath) && is_file($oldPath)) {
                    unlink($oldPath);
                }
                $aboutImage = $request->file('about_image');
                $aboutImageName = uploadImage($aboutImage, 'shops');
            } else {
                $aboutImageName = $user->shopInfo->about->about_image;
            }

            $user->first_name = $validated['first_name'];
            $user->last_name = $validated['last_name'];
            $user->email = $validated['email'];
            $user->phone = $validated['phone'];
            $user->company_name = $validated['company_name'];
            if ($avatarName) {
                $user->avatar = $avatarName;
            }
            $user->save();

            $user->shopInfo()->update([
                'shop_name' => $validated['shop_name'],
                'shop_city' => $validated['shop_city'],
                'shop_banner' => $shopBannerName,
                'shop_image' => $shopImageName
            ]);

            $user->shopInfo->about()->update([
                'tagline' => $validated['tagline'],
                'statement' => $validated['statement'],
                'our_story' => $validated['our_story'],
                'about_image' => $aboutImageName
            ]);

            $user->shopInfo->policies()->update([
                'shipping_information' => $validated['shipping_information'],
                'return_policy' => $validated['return_policy'],
                'payment_methods' => ($validated['payment_methods']),
            ]);

            if (isset($validated['faqs']) && is_array($validated['faqs'])) {
                $user->shopInfo->faqs()->delete(); // Remove existing FAQs

                foreach ($validated['faqs'] as $faq) {
                    $user->shopInfo->faqs()->create([
                        'question' => $faq['question'],
                        'answer' => $faq['answer'],
                    ]);
                }
            }

            // Save Social Links
            $user->shopInfo->socialLinks()->updateOrCreate([], [
                'website_url' => $validated['website_url'],
                'facebook_url' => $validated['facebook_url'],
                'instagram_url' => $validated['instagram_url'],
                'pinterest_url' => $validated['pinterest_url'],
            ]);

            $user->shopInfo->address()->update([
                'address_line_1' => $validated['address_line_1'],
                'address_line_2' => $validated['address_line_2'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'postal_code' => $validated['postal_code'],
                'display_my_address' => $validated['display_my_address'],
                'address_10_mile' => $validated['address_10_mile'],
                'do_not_display' => $validated['do_not_display'],
            ]);

            DB::commit();
            $user->load('shopInfo.address', 'shopInfo.socialLinks', 'shopInfo.about', 'shopInfo.policies', 'shopInfo.faqs');
            return $this->success($user, 'Shop owner data updated successfully', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error([], $e->getMessage(), 500);
        }
    }
}
