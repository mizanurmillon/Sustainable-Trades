<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlatRate extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'shop_id' => 'integer'
    ];

    public function shop()
    {
        return $this->belongsTo(ShopInfo::class, 'shop_id');
    }
}
