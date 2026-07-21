<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChallengeParticipant extends Model
{
    protected $fillable = [
        'challenge_id',
        'user_id',
        'current_count',
        'completed',
        'completed_at',
        'points_awarded',
    ];

    protected $casts = [
        'current_count' => 'integer',
        'completed' => 'boolean',
        'completed_at' => 'datetime',
        'points_awarded' => 'integer',
    ];

    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
