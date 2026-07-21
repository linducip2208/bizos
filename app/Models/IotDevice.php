<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IotDevice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'name',
        'device_token',
        'type',
        'model',
        'serial_number',
        'location',
        'status',
        'config',
        'metadata',
        'last_seen_at',
        'battery_level',
        'firmware_version',
        'installed_at',
        'next_maintenance_at',
        'is_active',
    ];

    protected $casts = [
        'config' => 'array',
        'metadata' => 'array',
        'last_seen_at' => 'datetime',
        'battery_level' => 'decimal:2',
        'installed_at' => 'date',
        'next_maintenance_at' => 'date',
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

    public function readings()
    {
        return $this->hasMany(IotReading::class);
    }

    public function alerts()
    {
        return $this->hasMany(IotAlert::class);
    }

    public function energyMeter()
    {
        return $this->hasOne(EnergyMeter::class);
    }

    public function smartScale()
    {
        return $this->hasOne(SmartScale::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOnline($query)
    {
        return $query->where('status', 'online');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
