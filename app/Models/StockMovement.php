<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockMovement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'product_id',
        'product_variant_id',
        'warehouse_id',
        'movement_type',
        'reference_type',
        'reference_id',
        'quantity_in',
        'quantity_out',
        'unit_cost',
        'running_quantity',
        'running_cost',
        'notes',
        'created_by',
        'movement_date',
    ];

    protected $casts = [
        'quantity_in' => 'decimal:4',
        'quantity_out' => 'decimal:4',
        'running_quantity' => 'decimal:4',
        'unit_cost' => 'decimal:2',
        'running_cost' => 'decimal:2',
        'movement_date' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
}
