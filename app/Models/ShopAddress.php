<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopAddress extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'status',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'shop_info_id' => 'integer',
        'display_my_address' => 'boolean',
        'address_10_mile' => 'boolean',
        'do_not_display' => 'boolean',
    ];

    public function shop()
    {
        return $this->belongsTo(ShopInfo::class, 'shop_info_id');
    }
}
