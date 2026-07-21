<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleMaintenanceLog extends Model
{
    protected $fillable = [
        'vehicle_id', 'type', 'description', 'cost', 'vendor',
        'odometer_at', 'next_odometer_due', 'date', 'next_due_date',
        'notes', 'attachment',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'odometer_at' => 'integer',
        'next_odometer_due' => 'integer',
        'date' => 'date',
        'next_due_date' => 'date',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
