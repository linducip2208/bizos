<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerformanceReviewScore extends Model
{
    protected $fillable = [
        'review_id',
        'indicator_id',
        'weight',
        'employee_score',
        'reviewer_score',
        'calibration_score',
        'comments',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'employee_score' => 'decimal:2',
        'reviewer_score' => 'decimal:2',
        'calibration_score' => 'decimal:2',
    ];

    public function review()
    {
        return $this->belongsTo(PerformanceReview::class, 'review_id');
    }

    public function indicator()
    {
        return $this->belongsTo(KpiIndicator::class, 'indicator_id');
    }
}
