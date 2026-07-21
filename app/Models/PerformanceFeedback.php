<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerformanceFeedback extends Model
{
    protected $fillable = [
        'review_id',
        'from_employee_id',
        'to_employee_id',
        'feedback_type',
        'rating',
        'strengths',
        'improvements',
        'is_anonymous',
        'submitted_at',
    ];

    protected $casts = [
        'feedback_type' => 'string',
        'rating' => 'integer',
        'is_anonymous' => 'boolean',
        'submitted_at' => 'datetime',
    ];

    public function review()
    {
        return $this->belongsTo(PerformanceReview::class, 'review_id');
    }

    public function fromEmployee()
    {
        return $this->belongsTo(Employee::class, 'from_employee_id');
    }

    public function toEmployee()
    {
        return $this->belongsTo(Employee::class, 'to_employee_id');
    }
}
