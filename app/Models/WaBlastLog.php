<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaBlastLog extends Model
{
    protected $fillable = [
        'campaign_id',
        'contact_phone',
        'contact_name',
        'message',
        'status',
        'error_message',
        'sent_at',
        'delivered_at',
        'read_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function campaign()
    {
        return $this->belongsTo(WaBlastCampaign::class, 'campaign_id');
    }
}
