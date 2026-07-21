<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleFuelLog extends Model
{
    protected $fillable = [
        'vehicle_id', 'driver_id', 'odometer', 'liters', 'cost',
        'fuel_type', 'station', 'receipt_photo', 'fuel_efficiency',
        'date', 'notes',
    ];

    protected $casts = [
        'odometer' => 'integer',
        'liters' => 'decimal:2',
        'cost' => 'decimal:2',
        'fuel_efficiency' => 'decimal:2',
        'date' => 'date',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(Employee::class, 'driver_id');
    }
}
