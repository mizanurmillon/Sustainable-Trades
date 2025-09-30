<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class shopOwnerRegisterRequest extends FormRequest
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
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'latitude'      => 'nullable|numeric',
            'longitude'     => 'nullable|numeric',
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
            'tagline' => 'required|string|max:50',
            'statement' => 'required|string|max:500',
            'our_story' => 'required|string|max:450',
            'about_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10240',
            'shipping_information' => 'required|string|max:75',
            'return_policy' => 'required|string|max:75',
            'payment_methods' => 'nullable|array',
            'payment_methods.*' => 'string|max:50',
            'answers' => 'required|array',
            'answers.*' => 'required|string',
            'questions' => 'required|array',
            'questions.*' => 'required|string',
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'password.min' => 'The password must be at least 8 characters long.',
            'agree_to_terms.required' => 'You must agree to the terms and conditions.',
        ];
    }
}
