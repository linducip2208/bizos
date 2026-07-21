<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillOfMaterial extends Model
{
    protected $table = 'bill_of_materials';

    protected $fillable = [
        'company_id',
        'product_id',
        'name',
        'revision',
        'quantity',
        'unit',
        'is_active',
        'effective_date',
        'obsolete_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'is_active' => 'boolean',
        'effective_date' => 'date',
        'obsolete_date' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function bomItems()
    {
        return $this->hasMany(BomItem::class, 'bom_id')->orderBy('sort_order');
    }

    public function routingOperations()
    {
        return $this->hasMany(RoutingOperation::class, 'bom_id');
    }

    public function productionOrders()
    {
        return $this->hasMany(ProductionOrder::class, 'bom_id');
    }

    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
}
