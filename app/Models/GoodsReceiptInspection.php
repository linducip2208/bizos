<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsReceiptInspection extends Model
{
    protected $fillable = [
        'goods_receipt_id',
        'grn_item_id',
        'quality_check_id',
        'result',
        'notes',
        'inspected_by',
        'inspected_at',
    ];

    protected $casts = [
        'inspected_at' => 'datetime',
    ];

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function grnItem()
    {
        return $this->belongsTo(GoodsReceiptItem::class, 'grn_item_id');
    }

    public function qualityCheck()
    {
        return $this->belongsTo(QualityCheck::class);
    }

    public function inspector()
    {
        return $this->belongsTo(Employee::class, 'inspected_by');
    }
}
