<?php

namespace App\Models;

use App\Concerns\HasApprovalWorkflow;
use App\Concerns\HasBranchScope;
use App\Contracts\Approvalable;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model implements Approvalable
{
    use HasBranchScope, HasApprovalWorkflow;

    public function getApprovalModule(): string { return 'leave'; }
    public function getApprovalTitle(): string { $emp = $this->employee; return 'Cuti: ' . ($emp ? trim($emp->first_name . ' ' . $emp->last_name) : 'Unknown') . ' — ' . ($this->leaveType?->name ?? 'Tanpa Tipe') . ' (' . ($this->start_date ? $this->start_date->format('d M') : '') . ' - ' . ($this->end_date ? $this->end_date->format('d M Y') : '') . ')'; }
    public function getApprovalRequesterId(): int { return $this->employee_id ?? 0; }
    public function getApprovalWorkflowName(): string { return 'Cuti'; }
    public function onApproved(): void { $this->update(['status' => 'approved']); }
    public function onRejected(string $reason): void { $this->update(['status' => 'rejected', 'rejection_reason' => $reason]); }

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'attachment',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_days' => 'integer',
        'status' => 'string',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function leaveApprovals()
    {
        return $this->hasMany(LeaveApproval::class);
    }
}
