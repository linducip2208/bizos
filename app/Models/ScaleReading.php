<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScaleReading extends Model
{
    protected $fillable = [
        'company_id',
        'smart_scale_id',
        'weight_kg',
        'weight_net_kg',
        'is_stable',
        'is_low_stock',
        'raw_payload',
        'recorded_at',
    ];

    protected $casts = [
        'weight_kg' => 'decimal:3',
        'weight_net_kg' => 'decimal:3',
        'is_stable' => 'boolean',
        'is_low_stock' => 'boolean',
        'raw_payload' => 'array',
        'recorded_at' => 'datetime',
    ];

    public function scale()
    {
        return $this->belongsTo(SmartScale::class, 'smart_scale_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeRecent($query)
    {
        return $query->orderByDesc('recorded_at');
    }
}
