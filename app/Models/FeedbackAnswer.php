<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedbackAnswer extends Model
{
    protected $fillable = [
        'reviewer_id',
        'question_id',
        'rating',
        'text_answer',
        'selected_options',
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'selected_options' => 'array',
    ];

    public function reviewer()
    {
        return $this->belongsTo(FeedbackReviewer::class, 'reviewer_id');
    }

    public function question()
    {
        return $this->belongsTo(FeedbackQuestion::class, 'question_id');
    }
}
