<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryRoute extends Model
{
    protected $table = 'routes';

    protected $fillable = [
        'company_id',
        'name',
        'driver_id',
        'vehicle_id',
        'date',
        'status',
        'total_distance',
        'total_time',
    ];

    protected $casts = [
        'date' => 'date',
        'total_distance' => 'decimal:2',
        'total_time' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function stops()
    {
        return $this->hasMany(RouteStop::class, 'route_id')->orderBy('stop_sequence');
    }
}
