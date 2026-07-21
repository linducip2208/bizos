<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalRequest extends Model
{
    protected $fillable = [
        'company_id',
        'workflow_id',
        'module',
        'module_id',
        'title',
        'requester_id',
        'status',
        'current_level',
        'total_levels',
        'submitted_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'module_id' => 'integer',
        'current_level' => 'integer',
        'total_levels' => 'integer',
        'submitted_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function workflow()
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'workflow_id');
    }

    public function module()
    {
        return $this->morphTo('module', 'module', 'module_id');
    }

    public function requester()
    {
        return $this->belongsTo(Employee::class, 'requester_id');
    }

    public function actions()
    {
        return $this->hasMany(ApprovalAction::class, 'approval_request_id');
    }

    public function currentApprovalLevel()
    {
        return $this->belongsTo(ApprovalLevel::class, 'current_level', 'level')
            ->where('workflow_id', $this->workflow_id);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForModule($query, string $module, int $moduleId)
    {
        return $query->where('module', $module)->where('module_id', $moduleId);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
