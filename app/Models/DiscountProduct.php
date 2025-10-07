<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountProduct extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'discount_id' => 'integer',
        'product_id' => 'integer',
    ];

    public function products()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class, 'discount_id');
    }   
}
