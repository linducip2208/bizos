<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GamificationBadge extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'category',
        'trigger_action',
        'trigger_count',
        'threshold_value',
        'threshold_unit',
        'points_reward',
        'color',
        'is_active',
    ];

    protected $casts = [
        'trigger_count' => 'integer',
        'threshold_value' => 'float',
        'points_reward' => 'integer',
        'is_active' => 'boolean',
    ];

    public function userBadges()
    {
        return $this->hasMany(UserBadge::class, 'badge_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_badges', 'badge_id', 'user_id')
            ->withPivot('awarded_at', 'context')
            ->withTimestamps();
    }
}
