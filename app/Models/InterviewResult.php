<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InterviewResult extends Model
{
    protected $fillable = [
        'interview_id',
        'interviewer_id',
        'rating',
        'comments',
        'recommendation',
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'recommendation' => 'string',
    ];

    public function interview()
    {
        return $this->belongsTo(Interview::class);
    }

    public function interviewer()
    {
        return $this->belongsTo(Interviewer::class);
    }
}
