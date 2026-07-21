<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractedEquipment extends Model
{
    protected $table = 'contracted_equipment';

    protected $fillable = [
        'service_contract_id',
        'equipment_name',
        'brand',
        'model',
        'serial_number',
        'location',
        'installation_date',
        'warranty_expiry',
        'last_service_date',
        'next_service_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'installation_date' => 'date',
        'warranty_expiry' => 'date',
        'last_service_date' => 'date',
        'next_service_date' => 'date',
    ];

    public function serviceContract()
    {
        return $this->belongsTo(ServiceContract::class);
    }

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }
}
