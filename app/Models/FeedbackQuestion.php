<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedbackQuestion extends Model
{
    protected $fillable = [
        'cycle_id',
        'question',
        'category',
        'question_type',
        'options',
        'sort_order',
    ];

    protected $casts = [
        'category' => 'string',
        'question_type' => 'string',
        'options' => 'array',
        'sort_order' => 'integer',
    ];

    public function cycle()
    {
        return $this->belongsTo(FeedbackCycle::class, 'cycle_id');
    }

    public function feedbackAnswers()
    {
        return $this->hasMany(FeedbackAnswer::class, 'question_id');
    }
}
