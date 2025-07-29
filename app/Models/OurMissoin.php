<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OurMissoin extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
    ];
}
