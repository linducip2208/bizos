<?php

namespace App\Models;

use App\Concerns\HasApprovalWorkflow;
use App\Contracts\Approvalable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseRequisition extends Model implements Approvalable
{
    use SoftDeletes, HasApprovalWorkflow;

    public function getApprovalModule(): string { return 'purchase_requisition'; }
    public function getApprovalTitle(): string { return 'PR #' . ($this->pr_number ?? $this->id) . ' — ' . ($this->department?->name ?? 'Tanpa Dept'); }
    public function getApprovalRequesterId(): int { return $this->requested_by ?? 0; }
    public function getApprovalWorkflowName(): string { return 'Permintaan Pembelian'; }
    public function onApproved(): void { $this->update(['status' => 'approved', 'approved_at' => now()]); }
    public function onRejected(string $reason): void { $this->update(['status' => 'rejected', 'rejection_reason' => $reason]); }

    protected $fillable = [
        'company_id',
        'pr_number',
        'department_id',
        'requested_by',
        'date_required',
        'notes',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'date_required' => 'date',
        'approved_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function requester()
    {
        return $this->belongsTo(Employee::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseRequisitionItem::class);
    }
}
