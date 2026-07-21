<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ColdChainLog extends Model
{
    protected $fillable = [
        'delivery_order_id',
        'sensor_id',
        'temperature_celsius',
        'humidity_percent',
        'recorded_at',
        'is_breached',
        'breach_details',
    ];

    protected $casts = [
        'temperature_celsius' => 'decimal:2',
        'humidity_percent' => 'decimal:2',
        'recorded_at' => 'datetime',
        'is_breached' => 'boolean',
    ];

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class);
    }
}
