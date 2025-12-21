<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopInfo extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'status',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function address()
    {
        return $this->hasOne(ShopAddress::class, 'shop_info_id');
    }

    public function socialLinks()
    {
        return $this->hasOne(ShopSocialLink::class, 'shop_info_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'shop_info_id');
    }

    public function policies()
    {
        return $this->hasOne(ShopPolicies::class, 'shop_info_id');
    }

    public function faqs()
    {
        return $this->hasMany(ShopFaq::class, 'shop_info_id');
    }

    public function about()
    {
        return $this->hasOne(AboutShop::class, 'shop_info_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'shop_info_id');
    }

    public function shopTax()
    {
        return $this->hasOne(ShopTax::class, 'shop_id');
    }
}
