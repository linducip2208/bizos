<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BpmnExecutionLog extends Model
{
    protected $table = 'bpmn_execution_logs';

    protected $fillable = [
        'process_instance_id', 'element_id', 'element_name', 'event_type',
        'event_data', 'actor_user_id', 'duration_seconds', 'logged_at',
    ];

    protected $casts = [
        'event_data' => 'json',
        'duration_seconds' => 'decimal:2',
        'logged_at' => 'datetime',
    ];

    public function processInstance()
    {
        return $this->belongsTo(BpmnProcessInstance::class, 'process_instance_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
