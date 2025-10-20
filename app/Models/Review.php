<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'shop_info_id' => 'integer',
        'user_id' => 'integer',
        'rating' => 'integer',
        'product_id' => 'integer',
    ];

    public function shop()
    {
        return $this->belongsTo(ShopInfo::class, 'shop_info_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function images()
    {
        return $this->hasMany(ReviewImage::class, 'review_id');
    }   

    
}
