<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BpmnProcessVariable extends Model
{
    protected $table = 'bpmn_process_variables';

    protected $fillable = [
        'process_instance_id', 'variable_name', 'variable_value', 'variable_type',
    ];

    public function processInstance()
    {
        return $this->belongsTo(BpmnProcessInstance::class, 'process_instance_id');
    }
}
