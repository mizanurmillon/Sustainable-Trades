<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id'=> 'integer',
        'shop_id'=> 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shop()
    {
        return $this->belongsTo(ShopInfo::class,'shop_id');
    }

    public function CartItems()
{
    return $this->hasMany(CartItem::class);
}
}
