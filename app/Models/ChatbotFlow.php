<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatbotFlow extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'description',
        'trigger_keywords',
        'welcome_message',
        'fallback_message',
        'is_active',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'trigger_keywords' => 'array',
        'is_active' => 'boolean',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function nodes()
    {
        return $this->hasMany(ChatbotFlowNode::class, 'flow_id');
    }

    public function edges()
    {
        return $this->hasMany(ChatbotFlowEdge::class, 'flow_id');
    }

    public function conversations()
    {
        return $this->hasMany(WaConversation::class, 'flow_id');
    }

    public function publish(): void
    {
        $this->update([
            'is_published' => true,
            'is_active' => true,
            'published_at' => now(),
        ]);
    }

    public function unpublish(): void
    {
        $this->update([
            'is_published' => false,
            'is_active' => false,
            'published_at' => null,
        ]);
    }
}
