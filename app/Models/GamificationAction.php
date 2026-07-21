<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GamificationAction extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'base_points',
        'category',
        'max_per_day',
        'is_active',
    ];

    protected $casts = [
        'base_points' => 'integer',
        'max_per_day' => 'integer',
        'is_active' => 'boolean',
    ];

    public function points()
    {
        return $this->hasMany(GamificationPoint::class, 'action_id');
    }
}
