<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EnergyMeter extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'iot_device_id',
        'name',
        'meter_number',
        'location',
        'rate_per_kwh',
        'utility_provider',
        'total_kwh_lifetime',
        'status',
        'is_active',
    ];

    protected $casts = [
        'rate_per_kwh' => 'decimal:2',
        'total_kwh_lifetime' => 'decimal:3',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function iotDevice()
    {
        return $this->belongsTo(IotDevice::class, 'iot_device_id');
    }

    public function readings()
    {
        return $this->hasMany(EnergyReading::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
