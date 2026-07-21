<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookDelivery extends Model
{
    protected $fillable = [
        'webhook_id',
        'request_payload',
        'response_code',
        'response_body',
        'duration_ms',
        'status',
        'attempt',
        'error_message',
        'next_retry_at',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_code' => 'integer',
        'duration_ms' => 'integer',
        'attempt' => 'integer',
        'next_retry_at' => 'datetime',
    ];

    public function webhook()
    {
        return $this->belongsTo(Webhook::class);
    }
}
