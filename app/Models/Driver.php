<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $fillable = [
        'company_id',
        'employee_id',
        'name',
        'phone',
        'license_number',
        'license_expiry',
        'status',
    ];

    protected $casts = [
        'license_expiry' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function routes()
    {
        return $this->hasMany(DeliveryRoute::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function isOnDelivery(): bool
    {
        return $this->status === 'on_delivery';
    }
}
