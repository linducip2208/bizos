<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EmailCampaign extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'subject',
        'sender_name',
        'sender_email',
        'template_content',
        'status',
        'scheduled_at',
        'sent_at',
        'total_recipients',
        'sent_count',
        'opened_count',
        'clicked_count',
        'bounced_count',
        'unsubscribed_count',
        'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'total_recipients' => 'integer',
        'sent_count' => 'integer',
        'opened_count' => 'integer',
        'clicked_count' => 'integer',
        'bounced_count' => 'integer',
        'unsubscribed_count' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function recipients()
    {
        return $this->hasMany(EmailCampaignRecipient::class, 'campaign_id');
    }

    public function getOpenRateAttribute(): float
    {
        if ($this->sent_count <= 0) {
            return 0;
        }

        return round(($this->opened_count / $this->sent_count) * 100, 2);
    }

    public function getClickRateAttribute(): float
    {
        if ($this->sent_count <= 0) {
            return 0;
        }

        return round(($this->clicked_count / $this->sent_count) * 100, 2);
    }

    public function trackOpens(): void
    {
        $this->increment('opened_count');
    }

    public function trackClicks(): void
    {
        $this->increment('clicked_count');
    }
}
