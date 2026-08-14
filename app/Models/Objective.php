<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Objective extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'title',
        'description',
        'objective_type',
        'owner_type',
        'owner_id',
        'parent_objective_id',
        'cycle_id',
        'start_date',
        'end_date',
        'status',
        'progress_percent',
        'weight',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'progress_percent' => 'decimal:2',
        'weight' => 'integer',
        'status' => 'string',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function owner(): MorphTo
    {
        return $this->morphTo('owner');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Objective::class, 'parent_objective_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Objective::class, 'parent_objective_id');
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(PerformanceCycle::class, 'cycle_id');
    }

    public function keyResults(): HasMany
    {
        return $this->hasMany(KeyResult::class, 'objective_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recalculateProgress(): float
    {
        $keyResults = $this->keyResults;

        if ($keyResults->isEmpty()) {
            $this->update(['progress_percent' => 0]);
            return 0;
        }

        $totalWeight = $keyResults->sum('weight');
        if ($totalWeight <= 0) {
            $this->update(['progress_percent' => 0]);
            return 0;
        }

        $weightedProgress = 0;
        foreach ($keyResults as $kr) {
            $weight = (float) $kr->weight;
            $weightedProgress += (float) $kr->progress_percent * ($weight / $totalWeight);
        }

        $progress = round($weightedProgress, 2);
        $this->update(['progress_percent' => $progress]);

        return $progress;
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

    public static function statusColorHex(): array
    {
        return [
            'draft' => '#6b7280',
            'active' => '#6366f1',
            'on_track' => '#10b981',
            'at_risk' => '#f59e0b',
            'behind' => '#ef4444',
            'completed' => '#10b981',
            'cancelled' => '#6b7280',
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
