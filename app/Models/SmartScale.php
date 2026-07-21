<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SmartScale extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'iot_device_id',
        'name',
        'scale_number',
        'location',
        'max_capacity_kg',
        'precision_g',
        'tare_weight_kg',
        'linked_product_id',
        'low_stock_threshold_kg',
        'current_weight_kg',
        'status',
        'is_active',
        'last_reading_at',
    ];

    protected $casts = [
        'max_capacity_kg' => 'decimal:2',
        'precision_g' => 'decimal:2',
        'tare_weight_kg' => 'decimal:3',
        'low_stock_threshold_kg' => 'decimal:3',
        'current_weight_kg' => 'decimal:3',
        'is_active' => 'boolean',
        'last_reading_at' => 'datetime',
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

    public function linkedProduct()
    {
        return $this->belongsTo(Product::class, 'linked_product_id');
    }

    public function readings()
    {
        return $this->hasMany(ScaleReading::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isLowStock(): bool
    {
        if ($this->low_stock_threshold_kg === null || $this->current_weight_kg === null) {
            return false;
        }
        return $this->current_weight_kg <= $this->low_stock_threshold_kg;
    }
}
