<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Forecast extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'forecast_type',
        'fiscal_year',
        'period_start',
        'period_end',
        'frequency',
        'version',
        'is_rolling',
        'baseline_budget_id',
        'total_amount',
        'status',
        'created_by',
    ];

    protected $casts = [
        'fiscal_year' => 'integer',
        'period_start' => 'date',
        'period_end' => 'date',
        'version' => 'integer',
        'is_rolling' => 'boolean',
        'total_amount' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function baselineBudget()
    {
        return $this->belongsTo(Budget::class, 'baseline_budget_id');
    }

    public function items()
    {
        return $this->hasMany(ForecastItem::class);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('forecast_type', $type);
    }

    public function scopeRolling(Builder $query): Builder
    {
        return $query->where('is_rolling', true);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }
}
