<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoodsReceiptItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'goods_receipt_id',
        'po_item_id',
        'product_id',
        'item_name',
        'unit',
        'quantity_received',
        'quantity_accepted',
        'quantity_rejected',
        'unit_price',
        'notes',
    ];

    protected $casts = [
        'quantity_received' => 'decimal:4',
        'quantity_accepted' => 'decimal:4',
        'quantity_rejected' => 'decimal:4',
        'unit_price' => 'decimal:2',
    ];

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function poItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'po_item_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
