<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaConversation extends Model
{
    protected $fillable = [
        'company_id',
        'contact_phone',
        'contact_name',
        'last_message',
        'last_message_at',
        'unread_count',
        'assigned_to',
        'status',
        'flow_id',
        'flow_state',
        'chatbot_intent',
        'chatbot_confidence',
        'last_bot_message_at',
        'is_bot_active',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'last_bot_message_at' => 'datetime',
        'unread_count' => 'integer',
        'flow_state' => 'array',
        'chatbot_confidence' => 'decimal:2',
        'is_bot_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    public function flow()
    {
        return $this->belongsTo(ChatbotFlow::class, 'flow_id');
    }

    public function activateBot(?int $flowId = null): void
    {
        $this->update([
            'is_bot_active' => true,
            'flow_id' => $flowId ?? $this->flow_id,
            'flow_state' => ['current_node_id' => null, 'data' => [], 'history' => []],
        ]);
    }

    public function deactivateBot(): void
    {
        $this->update([
            'is_bot_active' => false,
            'flow_state' => null,
        ]);
    }

    public function getCurrentFlowNodeId(): ?int
    {
        return $this->flow_state['current_node_id'] ?? null;
    }

    public function setFlowState(string $key, $value): void
    {
        $state = $this->flow_state ?? [];
        $state[$key] = $value;
        $this->update(['flow_state' => $state]);
    }

    public function addToFlowHistory(string $role, string $message): void
    {
        $state = $this->flow_state ?? [];
        $state['history'][] = [
            'role' => $role,
            'message' => $message,
            'at' => now()->toIso8601String(),
        ];
        $this->flow_state = $state;
        $this->save();
    }
}
