<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowExecution extends Model
{
    protected $fillable = [
        'workflow_id',
        'trigger_event',
        'input_context',
        'output_result',
        'status',
        'error_message',
        'duration_ms',
    ];

    protected $casts = [
        'input_context' => 'array',
        'output_result' => 'array',
        'duration_ms' => 'integer',
    ];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }
}
