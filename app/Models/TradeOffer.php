<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TradeOffer extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'sender_id' => 'integer',
        'receiver_id'=> 'integer',
    ];

    public function items()
    {
        return $this->hasMany(TradeItem::class);
    }

    public function attachments()
    {
        return $this->hasMany(TradeAttachment::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
