<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TechnicianVan extends Model
{
    protected $table = 'technician_vans';

    protected $fillable = [
        'technician_id',
        'vehicle_id',
        'license_plate',
        'current_location_lat',
        'current_location_lng',
        'last_location_update',
        'is_active',
    ];

    protected $casts = [
        'current_location_lat' => 'decimal:7',
        'current_location_lng' => 'decimal:7',
        'last_location_update' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function technician()
    {
        return $this->belongsTo(Employee::class, 'technician_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function inventories()
    {
        return $this->hasMany(VanInventory::class, 'van_id');
    }
}
