<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnergyReading extends Model
{
    protected $fillable = [
        'company_id',
        'energy_meter_id',
        'kwh',
        'voltage',
        'current_amps',
        'power_factor',
        'frequency_hz',
        'raw_payload',
        'recorded_at',
    ];

    protected $casts = [
        'kwh' => 'decimal:3',
        'voltage' => 'decimal:1',
        'current_amps' => 'decimal:1',
        'power_factor' => 'decimal:2',
        'frequency_hz' => 'decimal:2',
        'raw_payload' => 'array',
        'recorded_at' => 'datetime',
    ];

    public function meter()
    {
        return $this->belongsTo(EnergyMeter::class, 'energy_meter_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeRecent($query)
    {
        return $query->orderByDesc('recorded_at');
    }

    public function scopeBetweenDates($query, $from, $to)
    {
        return $query->whereBetween('recorded_at', [$from, $to]);
    }
}
