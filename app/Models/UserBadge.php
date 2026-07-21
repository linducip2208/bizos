<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBadge extends Model
{
    protected $fillable = [
        'user_id',
        'badge_id',
        'awarded_at',
        'context',
    ];

    protected $casts = [
        'awarded_at' => 'datetime',
        'context' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function badge()
    {
        return $this->belongsTo(GamificationBadge::class, 'badge_id');
    }
}
