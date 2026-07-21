<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RouteStop extends Model
{
    protected $fillable = [
        'route_id',
        'delivery_order_id',
        'stop_sequence',
        'address',
        'lat',
        'lng',
        'planned_arrival',
        'actual_arrival',
        'status',
    ];

    protected $casts = [
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'planned_arrival' => 'datetime',
        'actual_arrival' => 'datetime',
    ];

    public function route()
    {
        return $this->belongsTo(DeliveryRoute::class, 'route_id');
    }

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class);
    }
}
