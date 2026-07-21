<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotFlowNode extends Model
{
    protected $fillable = [
        'flow_id',
        'type',
        'label',
        'config',
        'position_x',
        'position_y',
    ];

    protected $casts = [
        'config' => 'array',
        'position_x' => 'integer',
        'position_y' => 'integer',
    ];

    public function flow()
    {
        return $this->belongsTo(ChatbotFlow::class, 'flow_id');
    }

    public function sourceEdges()
    {
        return $this->hasMany(ChatbotFlowEdge::class, 'source_node_id');
    }

    public function targetEdges()
    {
        return $this->hasMany(ChatbotFlowEdge::class, 'target_node_id');
    }

    public function getNextNodes(): array
    {
        return $this->sourceEdges()->with('targetNode')->get()
            ->pluck('targetNode')
            ->toArray();
    }

    public function getNextNodeForCondition(string $matchValue): ?self
    {
        $edge = $this->sourceEdges()
            ->whereJsonContains('condition->match', $matchValue)
            ->first();

        if (!$edge) {
            $edge = $this->sourceEdges()
                ->whereNull('condition')
                ->orWhereJsonLength('condition', 0)
                ->first();
        }

        return $edge?->targetNode;
    }
}
