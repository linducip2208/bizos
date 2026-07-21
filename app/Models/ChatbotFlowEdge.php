<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotFlowEdge extends Model
{
    protected $fillable = [
        'flow_id',
        'source_node_id',
        'target_node_id',
        'condition',
        'label',
    ];

    protected $casts = [
        'condition' => 'array',
    ];

    public function flow()
    {
        return $this->belongsTo(ChatbotFlow::class, 'flow_id');
    }

    public function sourceNode()
    {
        return $this->belongsTo(ChatbotFlowNode::class, 'source_node_id');
    }

    public function targetNode()
    {
        return $this->belongsTo(ChatbotFlowNode::class, 'target_node_id');
    }
}
