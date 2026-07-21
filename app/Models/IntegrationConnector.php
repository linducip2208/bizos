<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationConnector extends Model
{
    protected $fillable = [
        'company_id', 'connector_type', 'name', 'status',
        'credentials_encrypted', 'configuration', 'last_sync_result',
        'last_sync_at', 'last_error_at', 'last_error_message', 'is_active',
    ];

    protected $casts = [
        'credentials_encrypted' => 'array',
        'configuration' => 'array',
        'last_sync_result' => 'array',
        'last_sync_at' => 'datetime',
        'last_error_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function syncLogs()
    {
        return $this->hasMany(IntegrationSyncLog::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeConnected($query)
    {
        return $query->where('status', 'connected');
    }
}
