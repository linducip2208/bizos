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
        'is_active' => 'boolean',
        'run_count' => 'integer',
        'last_run_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function executions()
    {
        return $this->hasMany(WorkflowExecution::class)->orderBy('created_at', 'desc');
    }

    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
}
