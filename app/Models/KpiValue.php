<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiValue extends Model
{
    protected $fillable = [
        'kpi_definition_id',
        'period',
        'value',
        'target',
        'status',
        'calculated_at',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'target' => 'decimal:2',
        'calculated_at' => 'datetime',
    ];

    public function definition()
    {
        return $this->belongsTo(KpiDefinition::class, 'kpi_definition_id');
    }

    public function scopeOnTrack($query)
    {
        return $query->where('status', 'on_track');
    }

    public function scopeAtRisk($query)
    {
        return $query->where('status', 'at_risk');
    }

    public function scopeBehind($query)
    {
        return $query->where('status', 'behind');
    }
}
