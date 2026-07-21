<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedbackReviewer extends Model
{
    protected $fillable = [
        'cycle_id',
        'reviewee_id',
        'reviewer_id',
        'reviewer_type',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'reviewer_type' => 'string',
        'status' => 'string',
        'completed_at' => 'datetime',
    ];

    public function cycle()
    {
        return $this->belongsTo(FeedbackCycle::class, 'cycle_id');
    }

    public function reviewee()
    {
        return $this->belongsTo(Employee::class, 'reviewee_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(Employee::class, 'reviewer_id');
    }

    public function feedbackAnswers()
    {
        return $this->hasMany(FeedbackAnswer::class, 'reviewer_id');
    }
}
