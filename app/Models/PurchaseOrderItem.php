<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrderItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'pr_item_id',
        'item_name',
        'specification',
        'unit',
        'quantity',
        'received_qty',
        'unit_price',
        'tax_rate',
        'is_taxable',
        'discount_percent',
        'subtotal',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'received_qty' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'is_taxable' => 'boolean',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function prItem()
    {
        return $this->belongsTo(PurchaseRequisitionItem::class, 'pr_item_id');
    }

    public function goodsReceiptItems()
    {
        return $this->hasMany(GoodsReceiptItem::class, 'po_item_id');
    }
}
