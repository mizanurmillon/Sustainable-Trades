<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'shop_info_id' => 'integer',
        'unlimited_stock' => 'boolean',
        'out_of_stock' => 'boolean',
    ];

    public function shop()
    {
        return $this->belongsTo(ShopInfo::class, 'shop_info_id');
    }

    public function metaTags()
    {
        return $this->hasMany(MetaTag::class, 'product_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }
}
