<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EsgTarget extends Model
{
    protected $fillable = [
        'company_id', 'category', 'metric', 'metric_label', 'unit',
        'baseline_value', 'target_value', 'current_value', 'deadline',
        'status', 'description', 'responsible_person', 'framework_reference',
        'progress_history',
    ];

    protected $casts = [
        'baseline_value' => 'decimal:4',
        'target_value' => 'decimal:4',
        'current_value' => 'decimal:4',
        'deadline' => 'date',
        'progress_history' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function getProgressPercentAttribute(): float
    {
        if (!$this->baseline_value || !$this->target_value) {
            return 0;
        }

        $improvement = $this->baseline_value - $this->current_value;
        $targetImprovement = $this->baseline_value - $this->target_value;

        if ($targetImprovement == 0) {
            return 100;
        }

        return min(100, max(0, ($improvement / $targetImprovement) * 100));
    }

    public function getOnTrackAttribute(): bool
    {
        if ($this->status === 'achieved') return true;
        if ($this->status === 'abandoned') return false;

        $elapsed = now()->diffInDays($this->created_at);
        $total = $this->deadline->diffInDays($this->created_at);
        $expectedProgress = $total > 0 ? ($elapsed / $total) * 100 : 100;

        return $this->progress_percent >= $expectedProgress;
    }
}
