<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaAutoReply extends Model
{
    protected $fillable = [
        'company_id',
        'keyword',
        'match_type',
        'reply_text',
        'is_ai_powered',
        'ai_provider_id',
        'ai_prompt_template',
        'fallback_message',
        'intent_config',
        'escalation_rules',
        'is_active',
    ];

    protected $casts = [
        'is_ai_powered' => 'boolean',
        'is_active' => 'boolean',
        'intent_config' => 'array',
        'escalation_rules' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function aiProvider()
    {
        return $this->belongsTo(AiProvider::class, 'ai_provider_id');
    }
}
