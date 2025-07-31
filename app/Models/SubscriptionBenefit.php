<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionBenefit extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'subscription_plan_id' => 'integer',
    ];

    public function subscription_plan()
    {
        return $this->belongsTo(SubscriptionPlan::class,'subscription_plan_id');
    }
}
