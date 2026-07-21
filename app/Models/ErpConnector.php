<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErpConnector extends Model
{
    protected $fillable = [
        'company_id',
        'target_erp',
        'connection_config',
        'is_active',
        'last_synced_at',
    ];

    protected $casts = [
        'connection_config' => 'array',
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function syncLogs()
    {
        return $this->hasMany(ErpSyncLog::class, 'connector_id')->latest();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
