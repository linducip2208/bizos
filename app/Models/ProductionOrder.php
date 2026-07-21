<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionOrder extends Model
{
    protected $table = 'production_orders';

    protected $fillable = [
        'company_id',
        'po_number',
        'product_id',
        'bom_id',
        'work_center_id',
        'planned_quantity',
        'produced_quantity',
        'rejected_quantity',
        'planned_start',
        'planned_end',
        'actual_start',
        'actual_end',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'planned_quantity' => 'decimal:4',
        'produced_quantity' => 'decimal:4',
        'rejected_quantity' => 'decimal:4',
        'planned_start' => 'datetime',
        'planned_end' => 'datetime',
        'actual_start' => 'datetime',
        'actual_end' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function bom()
    {
        return $this->belongsTo(BillOfMaterial::class, 'bom_id');
    }

    public function workCenter()
    {
        return $this->belongsTo(WorkCenter::class);
    }

    public function materials()
    {
        return $this->hasMany(ProductionOrderMaterial::class);
    }

    public function operations()
    {
        return $this->hasMany(ProductionOrderOperation::class)->orderBy('id');
    }

    public function qcChecks()
    {
        return $this->hasMany(ProductionQcCheck::class);
    }

    public function wasteLogs()
    {
        return $this->hasMany(WasteLog::class);
    }

    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
}
