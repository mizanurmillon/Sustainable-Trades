<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopSocialLink extends Model
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
    ];

    public function shop()
    {
        return $this->belongsTo(ShopInfo::class, 'shop_info_id');
    }
}
