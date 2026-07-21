<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrderPart extends Model
{
    protected $table = 'work_order_parts';

    protected $fillable = [
        'work_order_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal',
        'from_van_stock',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'from_van_stock' => 'boolean',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
