<?php

namespace App\Models;

use App\Concerns\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id',
        'name',
        'channel',
        'email_campaign_id',
        'wa_blast_campaign_id',
        'status',
        'target_audience',
        'started_at',
        'completed_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'target_audience' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function emailCampaign()
    {
        return $this->belongsTo(EmailCampaign::class);
    }

    public function waBlastCampaign()
    {
        return $this->belongsTo(WaBlastCampaign::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
}
