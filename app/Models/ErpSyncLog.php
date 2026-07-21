<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErpSyncLog extends Model
{
    protected $fillable = [
        'connector_id',
        'entity_type',
        'direction',
        'records_count',
        'status',
        'error_message',
    ];

    public $timestamps = false;

    protected $casts = [
        'records_count' => 'integer',
    ];

    public function connector()
    {
        return $this->belongsTo(ErpConnector::class, 'connector_id');
    }
}
