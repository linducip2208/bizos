<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceContract extends Model
{
    protected $fillable = [
        'company_id',
        'client_id',
        'contract_number',
        'contract_type',
        'start_date',
        'end_date',
        'billing_cycle',
        'billing_amount',
        'service_frequency',
        'equipment_count',
        'status',
        'sla_response_hours',
        'sla_resolution_hours',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'billing_amount' => 'decimal:2',
        'equipment_count' => 'integer',
        'sla_response_hours' => 'integer',
        'sla_resolution_hours' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function contractedEquipment()
    {
        return $this->hasMany(ContractedEquipment::class);
    }

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }
}
