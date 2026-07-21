<?php

namespace App\Models;

use App\Concerns\HasApprovalWorkflow;
use App\Contracts\Approvalable;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model implements Approvalable
{
    use HasApprovalWorkflow;

    public function getApprovalModule(): string { return 'budget'; }
    public function getApprovalTitle(): string { return 'Anggaran: ' . ($this->name ?? 'Tanpa Nama') . ' — FY ' . ($this->fiscal_year ?? ''); }
    public function getApprovalRequesterId(): int { return $this->approved_by ?? 0; }
    public function getApprovalWorkflowName(): string { return 'Anggaran'; }
    public function onApproved(): void { $this->update(['status' => 'approved', 'approved_at' => now()]); }
    public function onRejected(string $reason): void { $this->update(['status' => 'rejected']); }

    protected $fillable = [
        'company_id',
        'name',
        'fiscal_year',
        'start_date',
        'end_date',
        'department_id',
        'project_id',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'fiscal_year' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
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

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function budgetItems()
    {
        return $this->hasMany(BudgetItem::class);
    }
}
