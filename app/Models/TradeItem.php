<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TradeItem extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'trade_offer_id' => 'integer',
        'product_id' => 'integer',
    ];

    public function tradeOffer()
    {
        return $this->belongsTo(TradeOffer::class, 'trade_offer_id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
