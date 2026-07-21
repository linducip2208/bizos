<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IotAlert extends Model
{
    protected $fillable = [
        'company_id',
        'iot_device_id',
        'type',
        'severity',
        'title',
        'message',
        'details',
        'status',
        'acknowledged_by',
        'acknowledged_at',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'details' => 'array',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(IotDevice::class, 'iot_device_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function acknowledgedBy()
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    public function scopeUnacknowledged($query)
    {
        return $query->whereNull('acknowledged_at');
    }

    public function acknowledge(?int $userId = null): void
    {
        $this->update([
            'status' => 'acknowledged',
            'acknowledged_by' => $userId ?? auth()->id(),
            'acknowledged_at' => now(),
        ]);
    }

    public function resolve(?int $userId = null): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_by' => $userId ?? auth()->id(),
            'resolved_at' => now(),
        ]);
    }
}
