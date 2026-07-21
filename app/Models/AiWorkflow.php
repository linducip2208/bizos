<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiWorkflow extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'description',
        'trigger_event',
        'agent_id',
        'steps',
        'is_active',
    ];

    protected $casts = [
        'steps' => 'array',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function agent()
    {
        return $this->belongsTo(AiAgent::class, 'agent_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
