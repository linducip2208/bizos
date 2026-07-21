<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    protected $table = 'work_orders';

    protected $fillable = [
        'company_id',
        'service_contract_id',
        'client_id',
        'equipment_id',
        'wo_number',
        'service_type',
        'priority',
        'description',
        'reported_by',
        'scheduled_start',
        'scheduled_end',
        'actual_start',
        'actual_end',
        'technician_id',
        'helper_id',
        'status',
        'resolution',
        'customer_signature_path',
        'photo_before_path',
        'photo_after_path',
        'gps_checkin_lat',
        'gps_checkin_lng',
        'gps_checkout_lat',
        'gps_checkout_lng',
        'travel_distance_km',
        'labor_hours',
        'parts_cost',
        'service_charge',
        'total_cost',
        'invoice_id',
        'customer_rating',
        'customer_feedback',
    ];

    protected $casts = [
        'scheduled_start' => 'datetime',
        'scheduled_end' => 'datetime',
        'actual_start' => 'datetime',
        'actual_end' => 'datetime',
        'gps_checkin_lat' => 'decimal:7',
        'gps_checkin_lng' => 'decimal:7',
        'gps_checkout_lat' => 'decimal:7',
        'gps_checkout_lng' => 'decimal:7',
        'travel_distance_km' => 'decimal:2',
        'labor_hours' => 'decimal:2',
        'parts_cost' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'customer_rating' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function serviceContract()
    {
        return $this->belongsTo(ServiceContract::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function equipment()
    {
        return $this->belongsTo(ContractedEquipment::class, 'equipment_id');
    }

    public function technician()
    {
        return $this->belongsTo(Employee::class, 'technician_id');
    }

    public function helper()
    {
        return $this->belongsTo(Employee::class, 'helper_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function parts()
    {
        return $this->hasMany(WorkOrderPart::class);
    }

    public function checklistItems()
    {
        return $this->hasMany(WorkOrderChecklistItem::class);
    }
}
