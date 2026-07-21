<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    protected $fillable = [
        'company_id',
        'work_center_id',
        'name',
        'model',
        'serial_number',
        'capacity_per_hour',
        'status',
    ];

    protected $casts = [
        'capacity_per_hour' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function workCenter()
    {
        return $this->belongsTo(WorkCenter::class);
    }

    public function productionOrders()
    {
        return $this->hasMany(ProductionOrder::class, 'machine_id');
    }
}
