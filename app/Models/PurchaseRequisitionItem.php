<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseRequisitionItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'purchase_requisition_id',
        'product_id',
        'item_name',
        'specification',
        'unit',
        'quantity',
        'estimated_price',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'estimated_price' => 'decimal:2',
    ];

    public function purchaseRequisition()
    {
        return $this->belongsTo(PurchaseRequisition::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
