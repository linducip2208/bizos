<?php

namespace App\Models;

use App\Concerns\HasApprovalWorkflow;
use App\Concerns\HasBranchScope;
use App\Contracts\Approvalable;
use Illuminate\Database\Eloquent\Model;

class Overtime extends Model implements Approvalable
{
    use HasBranchScope, HasApprovalWorkflow;

    public function getApprovalModule(): string { return 'overtime'; }
    public function getApprovalTitle(): string { $emp = $this->employee; return 'Lembur: ' . ($emp ? trim($emp->first_name . ' ' . $emp->last_name) : 'Unknown') . ' — ' . ($this->date ? $this->date->format('d M Y') : ''); }
    public function getApprovalRequesterId(): int { return $this->employee_id ?? 0; }
    public function getApprovalWorkflowName(): string { return 'Lembur'; }
    public function onApproved(): void { $this->update(['status' => 'approved', 'approved_at' => now()]); }
    public function onRejected(string $reason): void { $this->update(['status' => 'rejected']); }

    protected $fillable = [
        'employee_id',
        'date',
        'start_time',
        'end_time',
        'duration_minutes',
        'rate_multiplier',
        'reason',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'duration_minutes' => 'integer',
        'rate_multiplier' => 'decimal:2',
        'status' => 'string',
        'approved_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }
}
