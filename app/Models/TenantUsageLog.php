<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantUsageLog extends Model
{
    protected $fillable = [
        'company_id',
        'metric',
        'value',
        'recorded_at',
    ];

    protected $casts = [
        'value' => 'decimal:4',
        'recorded_at' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeForMetric($query, string $metric)
    {
        return $query->where('metric', $metric);
    }

    public function scopeForPeriod($query, string $from, string $to)
    {
        return $query->whereBetween('recorded_at', [$from, $to]);
    }

    public function scopeRecentDays($query, int $days = 30)
    {
        return $query->where('recorded_at', '>=', now()->subDays($days));
    }
}
