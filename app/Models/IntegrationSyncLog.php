<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationSyncLog extends Model
{
    protected $fillable = [
        'company_id', 'integration_connector_id', 'connector_type',
        'entity', 'direction', 'status', 'records_processed',
        'records_succeeded', 'records_failed', 'error_details',
        'summary', 'started_at', 'completed_at',
    ];

    protected $casts = [
        'records_processed' => 'integer',
        'records_succeeded' => 'integer',
        'records_failed' => 'integer',
        'error_details' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function connector()
    {
        return $this->belongsTo(IntegrationConnector::class, 'integration_connector_id');
    }
}
