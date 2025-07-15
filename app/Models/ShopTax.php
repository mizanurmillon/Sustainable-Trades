<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopTax extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'shop_id' => 'integer',
        'rate' => 'decimal:2',
        'is_digital_products' => 'boolean',
        'is_shipping' => 'boolean',
    ];

    public function shop()
    {
        return $this->belongsTo(ShopInfo::class, 'shop_id');
    }
}
