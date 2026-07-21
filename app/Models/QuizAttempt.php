<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    protected $fillable = [
        'quiz_id',
        'employee_id',
        'started_at',
        'submitted_at',
        'score',
        'total_points',
        'earned_points',
        'is_passed',
        'attempt_number',
        'violation_count',
        'violation_log',
        'is_auto_submitted',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'score' => 'decimal:2',
        'total_points' => 'integer',
        'earned_points' => 'integer',
        'is_passed' => 'boolean',
        'attempt_number' => 'integer',
        'violation_count' => 'integer',
        'violation_log' => 'array',
        'is_auto_submitted' => 'boolean',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function answers()
    {
        return $this->hasMany(QuizAttemptAnswer::class, 'attempt_id');
    }
}
