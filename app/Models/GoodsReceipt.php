<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoodsReceipt extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'grn_number',
        'purchase_order_id',
        'warehouse_id',
        'received_by',
        'receipt_date',
        'delivery_note',
        'invoice_number',
        'notes',
        'status',
        'posted_at',
        'invoice_id',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'posted_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function receiver()
    {
        return $this->belongsTo(Employee::class, 'received_by');
    }

    public function items()
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
