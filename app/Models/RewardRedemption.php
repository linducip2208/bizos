<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardRedemption extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'reward_id',
        'points_spent',
        'status',
        'notes',
        'redeemed_at',
    ];

    protected $casts = [
        'points_spent' => 'integer',
        'redeemed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reward()
    {
        return $this->belongsTo(Reward::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
