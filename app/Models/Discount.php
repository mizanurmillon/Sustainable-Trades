<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'shop_id' => 'integer',
        'never_expires' => 'boolean',
        'product_id' => 'integer',
    ];

    public function shop()
    {
        return $this->belongsTo(ShopInfo::class, 'shop_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function discountProducts()
    {
        return $this->hasMany(DiscountProduct::class, 'discount_id');
    }
}
