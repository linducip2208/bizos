<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workflow extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'description',
        'workflow_type',
        'trigger_event',
        'trigger_conditions',
        'actions',
        'bpmn_xml',
        'bpmn_svg',
        'approval_levels',
        'sla_hours',
        'module',
        'min_approvers',
        'category',
        'studio_config',
        'enabled_blocks',
        'webhook_url',
        'schedule_cron',
        'is_active',
        'run_count',
        'last_run_at',
        'created_by',
    ];

    protected $casts = [
        'trigger_conditions' => 'array',
        'actions' => 'array',
        'studio_config' => 'array',
        'enabled_blocks' => 'array',
        'approval_levels' => 'array',
        'bpmn_xml' => 'string',
        'bpmn_svg' => 'string',
        'is_active' => 'boolean',
        'min_approvers' => 'integer',
        'sla_hours' => 'integer',
        'run_count' => 'integer',
        'last_run_at' => 'datetime',
    ];

    const TYPE_SIMPLE = 'simple';
    const TYPE_APPROVAL = 'approval';
    const TYPE_BPMN = 'bpmn';
    const TYPE_AUTOMATION = 'automation';

    public static function types(): array
    {
        return [
            self::TYPE_SIMPLE => 'IFTTT Simpel',
            self::TYPE_APPROVAL => 'Approval Berantai',
            self::TYPE_BPMN => 'BPMN 2.0',
            self::TYPE_AUTOMATION => 'Otomasi Lanjutan',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function executions()
    {
        return $this->hasMany(WorkflowExecution::class)->orderBy('created_at', 'desc');
    }

    public function bpmnInstances()
    {
        return $this->hasMany(BpmnProcessInstance::class, 'unified_workflow_id');
    }

    public function approvalRequests()
    {
        return $this->hasMany(ApprovalRequest::class, 'unified_workflow_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('workflow_type', $type);
    }

    public function scopeForModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    public function scopeByTrigger($query, string $event)
    {
        return $query->where('trigger_event', $event);
    }

    public function isSimple(): bool
    {
        return $this->workflow_type === self::TYPE_SIMPLE;
    }

    public function isApproval(): bool
    {
        return $this->workflow_type === self::TYPE_APPROVAL;
    }

    public function isBpmn(): bool
    {
        return $this->workflow_type === self::TYPE_BPMN;
    }

    public function isAutomation(): bool
    {
        return $this->workflow_type === self::TYPE_AUTOMATION;
    }
}
