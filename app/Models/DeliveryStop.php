<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryStop extends Model
{
    protected $fillable = [
        'delivery_order_id',
        'stop_sequence',
        'address',
        'contact_name',
        'contact_phone',
        'planned_arrival',
        'actual_arrival',
        'status',
        'gps_lat',
        'gps_lng',
        'notes',
    ];

    protected $casts = [
        'stop_sequence' => 'integer',
        'planned_arrival' => 'datetime',
        'actual_arrival' => 'datetime',
    ];

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class);
    }
}
