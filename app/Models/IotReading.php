<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IotReading extends Model
{
    protected $fillable = [
        'company_id',
        'iot_device_id',
        'temperature_celsius',
        'humidity_percent',
        'vibration_mm_s',
        'pressure_hpa',
        'battery_level',
        'signal_strength_dbm',
        'raw_payload',
        'extra_data',
        'recorded_at',
    ];

    protected $casts = [
        'temperature_celsius' => 'decimal:2',
        'humidity_percent' => 'decimal:2',
        'vibration_mm_s' => 'decimal:4',
        'pressure_hpa' => 'decimal:2',
        'battery_level' => 'decimal:2',
        'signal_strength_dbm' => 'decimal:2',
        'raw_payload' => 'array',
        'extra_data' => 'array',
        'recorded_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(IotDevice::class, 'iot_device_id');
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
