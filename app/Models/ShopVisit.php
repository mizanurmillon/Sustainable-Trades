<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopVisit extends Model
{
    protected $fillable = [
        'shop_id',
        'visitor_ip',
        'visited_at',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'shop_id' => 'integer',
        'visited_at' => 'datetime',
    ];

    public function shop()
    {
        return $this->belongsTo(ShopInfo::class, 'shop_id');
    }
}
