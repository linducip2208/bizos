<?php

namespace App\Models;

use App\Concerns\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class FleetGpsTrack extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'vehicle_id',
        'driver_id',
        'latitude',
        'longitude',
        'speed_kmh',
        'heading',
        'ignition_status',
        'recorded_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'speed_kmh' => 'decimal:1',
        'heading' => 'decimal:1',
        'ignition_status' => 'boolean',
        'recorded_at' => 'datetime',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(Employee::class, 'driver_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
