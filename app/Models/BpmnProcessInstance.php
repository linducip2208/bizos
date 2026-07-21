<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BpmnProcessInstance extends Model
{
    protected $table = 'bpmn_process_instances';

    protected $fillable = [
        'process_id', 'company_id', 'instance_code', 'status',
        'current_element_id', 'current_element_name', 'process_variables',
        'started_by', 'started_at', 'completed_at', 'error_message',
    ];

    protected $casts = [
        'process_variables' => 'json',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function process()
    {
        return $this->belongsTo(BpmnProcess::class, 'process_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function startedBy()
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    public function taskInstances()
    {
        return $this->hasMany(BpmnTaskInstance::class, 'process_instance_id');
    }

    public function variables()
    {
        return $this->hasMany(BpmnProcessVariable::class, 'process_instance_id');
    }

    public function executionLogs()
    {
        return $this->hasMany(BpmnExecutionLog::class, 'process_instance_id');
    }
}
