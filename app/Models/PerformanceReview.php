<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerformanceReview extends Model
{
    protected $fillable = [
        'cycle_id',
        'employee_id',
        'reviewer_id',
        'kpi_template_id',
        'employee_self_score',
        'reviewer_score',
        'calibration_score',
        'final_score',
        'status',
        'self_submitted_at',
        'review_submitted_at',
        'calibration_at',
    ];

    protected $casts = [
        'employee_self_score' => 'decimal:2',
        'reviewer_score' => 'decimal:2',
        'calibration_score' => 'decimal:2',
        'final_score' => 'decimal:2',
        'status' => 'string',
        'self_submitted_at' => 'datetime',
        'review_submitted_at' => 'datetime',
        'calibration_at' => 'datetime',
    ];

    public function cycle()
    {
        return $this->belongsTo(PerformanceCycle::class, 'cycle_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(Employee::class, 'reviewer_id');
    }

    public function kpiTemplate()
    {
        return $this->belongsTo(KpiTemplate::class, 'kpi_template_id');
    }

    public function scores()
    {
        return $this->hasMany(PerformanceReviewScore::class, 'review_id');
    }

    public function feedback()
    {
        return $this->hasMany(PerformanceFeedback::class, 'review_id');
    }

    public function getRatingAttribute(): string
    {
        $score = $this->final_score ?? $this->reviewer_score ?? $this->employee_self_score ?? 0;
        return match (true) {
            $score >= 90 => 'A',
            $score >= 75 => 'B',
            $score >= 60 => 'C',
            $score >= 40 => 'D',
            default => 'E',
        };
    }

    public function getRatingLabelAttribute(): string
    {
        return match ($this->rating) {
            'A' => 'Istimewa',
            'B' => 'Melampaui Ekspektasi',
            'C' => 'Memenuhi Ekspektasi',
            'D' => 'Di Bawah Ekspektasi',
            'E' => 'Tidak Memuaskan',
            default => '-',
        };
    }
}
