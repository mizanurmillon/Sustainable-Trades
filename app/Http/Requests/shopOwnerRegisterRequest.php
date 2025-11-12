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
            'email'          => 'required|email|unique:users,email',
            'phone'          => 'required|string|max:20|unique:users,phone',
            'company_name'  => 'required|string|max:255',
            'shop_name'    => 'required|string|max:255|unique:shop_infos,shop_name',
            'shop_city'   => 'required|string|max:255',
            'avatar'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10240', // 10MB max
            'shop_image'    => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10240', // 10MB max
            'shop_banner'  => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10240', // 10MB max
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
            'zip_code'      => 'required|string|max:5',
            'display_my_address' => 'boolean',
            'address_10_mile' => 'boolean',
            'do_not_display' => 'boolean',
            'password'       => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'tagline' => 'required|string',
            'statement' => 'required|string',
            'our_story' => 'required|string',
            'about_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:10240',
            'shipping_information' => 'required|string',
            'return_policy' => 'required|string',
            'payment_methods' => 'nullable|array',
            'payment_methods.*' => 'string',
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
