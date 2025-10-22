<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatusHistory extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'order_id' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
