<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiProvider extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'api_format',
        'base_url',
        'api_key_encrypted',
        'default_model',
        'extra_headers',
        'is_active',
    ];

    protected $casts = [
        'extra_headers' => 'array',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function aiConversations()
    {
        return $this->hasMany(AiConversation::class, 'provider_id');
    }
}
