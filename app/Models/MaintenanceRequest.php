<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    protected $fillable = [
        'company_id',
        'property_unit_id',
        'tenancy_contract_id',
        'requested_by',
        'category',
        'description',
        'priority',
        'status',
        'assigned_to',
        'completed_at',
        'cost',
        'notes',
        'work_order_id',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'cost' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function propertyUnit()
    {
        return $this->belongsTo(PropertyUnit::class);
    }

    public function tenancyContract()
    {
        return $this->belongsTo(TenancyContract::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class, 'work_order_id');
    }
}
