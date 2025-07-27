<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipHistory extends Model
{
    protected $guarded = [];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'plan_id' => 'integer',
        'membership_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function membership()
    {
        return $this->belongsTo(Membership::class, 'membership_id');
    }
}
