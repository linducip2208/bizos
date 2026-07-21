<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaBlastCampaign extends Model
{
    protected $fillable = [
        'company_id',
        'template_id',
        'name',
        'target_type',
        'target_segment_id',
        'target_clients',
        'scheduled_at',
        'sent_at',
        'total_targets',
        'total_sent',
        'total_failed',
        'status',
    ];

    protected $casts = [
        'target_clients' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'total_targets' => 'integer',
        'total_sent' => 'integer',
        'total_failed' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function template()
    {
        return $this->belongsTo(WaTemplate::class, 'template_id');
    }

    public function targetSegment()
    {
        return $this->belongsTo(ClientSegment::class, 'target_segment_id');
    }

    public function waBlastLogs()
    {
        return $this->hasMany(WaBlastLog::class, 'campaign_id');
    }
}
