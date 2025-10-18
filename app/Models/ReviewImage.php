<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewImage extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'review_id' => 'integer',
    ];

    public function review()
    {
        return $this->belongsTo(Review::class, 'review_id');
    }
}
