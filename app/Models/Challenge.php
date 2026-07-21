<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    protected $fillable = [
        'company_id',
        'title',
        'description',
        'target_action',
        'target_count',
        'points_reward',
        'start_date',
        'end_date',
        'category',
        'is_active',
    ];

    protected $casts = [
        'target_count' => 'integer',
        'points_reward' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function participants()
    {
        return $this->hasMany(ChallengeParticipant::class);
    }

    public function getProgressPercentAttribute(): float
    {
        $total = $this->participants()->count();
        if ($total === 0) return 0;
        $completed = $this->participants()->where('completed', true)->count();
        return round(($completed / $total) * 100, 1);
    }
}
