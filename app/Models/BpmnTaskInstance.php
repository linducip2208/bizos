<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BpmnTaskInstance extends Model
{
    protected $table = 'bpmn_task_instances';

    protected $fillable = [
        'process_instance_id', 'element_id', 'task_name', 'type', 'status',
        'gateway_type', 'gateway_default_flow', 'assignee_user_id', 'assignee_role',
        'input_variables', 'output_variables', 'sla_hours', 'priority',
        'started_at', 'completed_at', 'sla_deadline', 'next_element_id', 'error_message',
    ];

    protected $casts = [
        'input_variables' => 'json',
        'output_variables' => 'json',
        'sla_hours' => 'decimal:2',
        'priority' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'sla_deadline' => 'datetime',
    ];

    public function processInstance()
    {
        return $this->belongsTo(BpmnProcessInstance::class, 'process_instance_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_user_id');
    }
}
