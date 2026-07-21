<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    protected $fillable = [
        'company_id', 'gateway_id', 'recipient', 'message',
        'status', 'message_id', 'cost', 'error_message',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function gateway()
    {
        return $this->belongsTo(SmsGateway::class, 'gateway_id');
    }
}
