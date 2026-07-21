<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = [
        'company_id', 'plate_number', 'brand', 'model', 'year',
        'vehicle_type', 'fuel_type', 'ownership', 'status',
        'last_odometer', 'color', 'chassis_number', 'engine_number',
        'registration_expiry', 'insurance_expiry', 'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'last_odometer' => 'integer',
        'registration_expiry' => 'date',
        'insurance_expiry' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function fuelLogs()
    {
        return $this->hasMany(VehicleFuelLog::class);
    }

    public function maintenanceLogs()
    {
        return $this->hasMany(VehicleMaintenanceLog::class);
    }

    public function assignments()
    {
        return $this->hasMany(VehicleAssignment::class);
    }

    public function currentAssignment()
    {
        return $this->hasOne(VehicleAssignment::class)->whereNull('returned_at')->latest('assigned_at');
    }

    public function currentDriver()
    {
        return $this->hasOneThrough(Employee::class, VehicleAssignment::class, 'vehicle_id', 'id', 'id', 'employee_id')
            ->whereNull('vehicle_assignments.returned_at')
            ->latest('vehicle_assignments.assigned_at');
    }
}
