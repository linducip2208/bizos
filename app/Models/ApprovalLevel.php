<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalLevel extends Model
{
    protected $fillable = [
        'workflow_id',
        'level',
        'approver_type',
        'approver_id',
        'is_required',
        'can_delegate',
        'sla_hours',
        'sla_action',
    ];

    protected $casts = [
        'level' => 'integer',
        'approver_id' => 'integer',
        'is_required' => 'boolean',
        'can_delegate' => 'boolean',
        'sla_hours' => 'integer',
        'sla_action' => 'string',
    ];

    public function workflow()
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'workflow_id');
    }

    public function actions()
    {
        return $this->hasMany(ApprovalAction::class, 'level_id');
    }

    public function getApproverAttribute()
    {
        return match ($this->approver_type) {
            'employee' => Employee::find($this->approver_id),
            'role' => User::where('role_id', $this->approver_id)->with('employee')->get()->pluck('employee'),
            'department' => Employee::where('department_id', $this->approver_id)->get(),
            'position' => Employee::where('position_id', $this->approver_id)->get(),
            default => collect(),
        };
    }

    public function getApproverEmployeeIds(): array
    {
        return match ($this->approver_type) {
            'employee' => [$this->approver_id],
            'role' => User::where('role_id', $this->approver_id)
                ->whereNotNull('employee_id')
                ->pluck('employee_id')
                ->toArray(),
            'department' => Employee::where('department_id', $this->approver_id)
                ->pluck('id')
                ->toArray(),
            'position' => Employee::where('position_id', $this->approver_id)
                ->pluck('id')
                ->toArray(),
            default => [],
        };
    }
}
