<?php

namespace App\Models;

use App\Concerns\HasApprovalWorkflow;
use App\Concerns\HasBranchScope;
use App\Contracts\Approvalable;
use Illuminate\Database\Eloquent\Model;

class Reimbursement extends Model implements Approvalable
{
    use HasBranchScope, HasApprovalWorkflow;

    public function getApprovalModule(): string { return 'reimbursement'; }
    public function getApprovalTitle(): string { $emp = $this->employee; $cat = $this->category; return "Reimbursement: " . ($emp ? trim($emp->first_name . ' ' . $emp->last_name) : 'Unknown') . ' — ' . ($cat?->name ?? 'Tanpa Kategori'); }
    public function getApprovalRequesterId(): int { return $this->employee_id ?? 0; }
    public function getApprovalWorkflowName(): string { return 'Reimbursement'; }
    public function onApproved(): void { $this->update(['status' => 'approved']); }
    public function onRejected(string $reason): void { $this->update(['status' => 'rejected', 'rejection_reason' => $reason]); }

    protected $fillable = [
        'employee_id',
        'category_id',
        'date',
        'amount',
        'description',
        'status',
        'rejection_reason',
        'paid_date',
        'paid_amount',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'status' => 'string',
        'paid_date' => 'date',
        'paid_amount' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function category()
    {
        return $this->belongsTo(ReimbursementCategory::class, 'category_id');
    }

    public function reimbursementAttachments()
    {
        return $this->hasMany(ReimbursementAttachment::class);
    }
}
