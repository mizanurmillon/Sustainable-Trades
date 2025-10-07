<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class shopOwnerUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name'  => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email'          => 'required|email|unique:users,email,' . (auth()->user()->id ?? 'null'),
            'phone'          => 'required|string|max:15|unique:users,phone,' . (auth()->user()->id ?? 'null'),
            'company_name'  => 'required|string|max:255',
            'shop_name'    => 'required|string|max:255|unique:shop_infos,shop_name,' . (auth()->user()->shopInfo->id ?? 'null'),
            'shop_city'   => 'required|string|max:255',
            'avatar'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10240', // 10MB max
            'shop_image'    => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10240', // 10MB max
            'shop_banner'  => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10240', // 10MB max
            'website_url'  => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'pinterest_url'  => 'nullable|url|max:255',
            // 'address_line_1' => 'nullable|string|max:255',
            // 'address_line_2' => 'nullable|string|max:255',
            // 'latitude'      => 'nullable|numeric',
            // 'longitude'     => 'nullable|numeric',
            // 'city'          => 'nullable|string|max:255',
            // 'state'         => 'nullable|string|max:255',
            // 'zip_code'      => 'nullable|string|max:20',
            'display_my_address' => 'boolean',
            'address_10_mile' => 'boolean',
            'do_not_display' => 'boolean',
            'tagline' => 'required|string',
            'statement' => 'required|string',
            'our_story' => 'required|string',
            'about_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10240',
            'shipping_information' => 'required|string',
            'return_policy' => 'required|string',
            // 'payment_methods' => 'nullable|array',
            // 'payment_methods.*' => 'string|max:50',
            'answers' => 'nullable|array',
            'answers.*' => 'nullable|string',
            'questions' => 'nullable|array',
            'questions.*' => 'nullable|string',
        ];
    }
    
}
