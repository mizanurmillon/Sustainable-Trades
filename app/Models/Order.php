<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'shop_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shop()
    {
        return $this->belongsTo(ShopInfo::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function OrderStatusHistory()
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function shippingAddress()
    {
        return $this->hasOne(ShippingAddress::class);
    }
}
