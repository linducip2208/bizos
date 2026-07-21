<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiAgent extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'description',
        'system_prompt',
        'model',
        'provider_id',
        'tools',
        'is_active',
    ];

    protected $casts = [
        'tools' => 'array',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function provider()
    {
        return $this->belongsTo(AiProvider::class, 'provider_id');
    }

    public function workflows()
    {
        return $this->hasMany(AiWorkflow::class, 'agent_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
