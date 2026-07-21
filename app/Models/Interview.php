<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interview extends Model
{
    protected $fillable = [
        'candidate_id',
        'interview_type',
        'scheduled_at',
        'duration_minutes',
        'location',
        'meeting_link',
        'notes',
        'status',
    ];

    protected $casts = [
        'interview_type' => 'string',
        'scheduled_at' => 'datetime',
        'duration_minutes' => 'integer',
        'status' => 'string',
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function interviewers()
    {
        return $this->hasMany(Interviewer::class);
    }

    public function interviewResults()
    {
        return $this->hasMany(InterviewResult::class);
    }
}
