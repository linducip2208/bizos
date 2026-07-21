<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedbackCycle extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => 'string',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function feedbackQuestions()
    {
        return $this->hasMany(FeedbackQuestion::class, 'cycle_id');
    }

    public function feedbackReviewers()
    {
        return $this->hasMany(FeedbackReviewer::class, 'cycle_id');
    }
}
