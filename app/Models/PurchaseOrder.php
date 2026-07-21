<?php

namespace App\Models;

use App\Concerns\HasApprovalWorkflow;
use App\Contracts\Approvalable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model implements Approvalable
{
    use SoftDeletes, HasApprovalWorkflow;

    public function getApprovalModule(): string { return 'purchase_order'; }
    public function getApprovalTitle(): string { return 'PO #' . ($this->po_number ?? $this->id) . ' — ' . ($this->supplier?->name ?? 'Tanpa Supplier'); }
    public function getApprovalRequesterId(): int { return $this->created_by ?? 0; }
    public function getApprovalWorkflowName(): string { return 'Pesanan Pembelian'; }
    public function onApproved(): void { $this->update(['status' => 'approved', 'approved_at' => now()]); }
    public function onRejected(string $reason): void { $this->update(['status' => 'cancelled']); }

    protected $fillable = [
        'company_id',
        'po_number',
        'supplier_id',
        'pr_id',
        'warehouse_id',
        'order_date',
        'expected_date',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'shipping_cost',
        'total',
        'notes',
        'terms_conditions',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'invoice_id',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseRequisition()
    {
        return $this->belongsTo(PurchaseRequisition::class, 'pr_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    public function goodsReceipts()
    {
        return $this->hasMany(GoodsReceipt::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
