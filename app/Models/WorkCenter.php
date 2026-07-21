<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkCenter extends Model
{
    use SoftDeletes;

    protected $table = 'work_centers';

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'type',
        'capacity_per_day',
        'capacity_uom',
        'hourly_cost',
        'overhead_rate_percent',
        'is_active',
    ];

    protected $casts = [
        'capacity_per_day' => 'decimal:2',
        'hourly_cost' => 'decimal:2',
        'overhead_rate_percent' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function routingOperations()
    {
        return $this->hasMany(RoutingOperation::class);
    }

    public function productionOrders()
    {
        return $this->hasMany(ProductionOrder::class);
    }

    public function productionOrderOperations()
    {
        return $this->hasMany(ProductionOrderOperation::class);
    }
}
