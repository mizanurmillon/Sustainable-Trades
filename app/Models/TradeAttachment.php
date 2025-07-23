<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TradeAttachment extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'trade_offer_id' => 'integer',
    ];

    public function tradeOffer()
    {
        return $this->belongsTo(TradeOffer::class, 'trade_offer_id');
    }
}
