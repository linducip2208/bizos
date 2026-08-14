<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KeyResult extends Model
{
    protected $fillable = [
        'objective_id',
        'title',
        'description',
        'metric_type',
        'start_value',
        'current_value',
        'target_value',
        'unit',
        'progress_percent',
        'status',
        'weight',
        'due_date',
        'check_in_frequency',
        'created_by',
    ];

    protected $casts = [
        'start_value' => 'decimal:4',
        'current_value' => 'decimal:4',
        'target_value' => 'decimal:4',
        'progress_percent' => 'decimal:2',
        'weight' => 'decimal:2',
        'due_date' => 'date',
        'status' => 'string',
    ];

    public function objective(): BelongsTo
    {
        return $this->belongsTo(Objective::class, 'objective_id');
    }

    public function checkIns(): HasMany
    {
        return $this->hasMany(KeyResultCheckIn::class, 'key_result_id')->latest('created_at');
    }

    public function latestCheckIn()
    {
        return $this->hasOne(KeyResultCheckIn::class, 'key_result_id')->latestOfMany('created_at');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function calculateProgress(?float $currentValue = null): float
    {
        $target = (float) $this->target_value;
        $current = $currentValue ?? (float) $this->current_value;
        $start = (float) $this->start_value;

        if ($target <= 0 && $start <= 0) {
            return 0;
        }

        if ($this->metric_type === 'boolean') {
            return $current >= 1 ? 100 : 0;
        }

        if ($this->metric_type === 'milestone') {
            return match ($this->status) {
                'completed' => 100,
                'on_track' => 70,
                'at_risk' => 40,
                'behind' => 15,
                'active' => 20,
                default => 0,
            };
        }

        $range = $target - $start;
        if ($range <= 0) {
            return 0;
        }

        return min(round((($current - $start) / $range) * 100, 2), 100);
    }

    public function syncFromLatestCheckIn(): void
    {
        $latestCheckIn = $this->latestCheckIn;
        if ($latestCheckIn) {
            $newCurrent = (float) $latestCheckIn->value;
            $this->current_value = $newCurrent;
            $this->progress_percent = $this->calculateProgress($newCurrent);
            $this->save();
        }
    }

    public static function metricTypeOptions(): array
    {
        return [
            'percentage' => 'Persentase',
            'number' => 'Angka',
            'currency' => 'Mata Uang',
            'boolean' => 'Ya/Tidak',
            'milestone' => 'Milestone',
        ];
    }

    public static function statusOptions(): array
    {
        return [
            'draft' => 'Draft',
            'active' => 'Aktif',
            'on_track' => 'On Track',
            'at_risk' => 'Berisiko',
            'behind' => 'Tertinggal',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];
    }

    public static function statusColors(): array
    {
        return [
            'draft' => 'gray',
            'active' => 'primary',
            'on_track' => 'success',
            'at_risk' => 'warning',
            'behind' => 'danger',
            'completed' => 'success',
            'cancelled' => 'gray',
        ];
    }

    public static function frequencyOptions(): array
    {
        return [
            'weekly' => 'Mingguan',
            'biweekly' => 'Dua Mingguan',
            'monthly' => 'Bulanan',
            'quarterly' => 'Kuartalan',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return static::statusOptions()[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return static::statusColors()[$this->status] ?? 'gray';
    }
}
