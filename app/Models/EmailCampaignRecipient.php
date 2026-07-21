<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EmailCampaignRecipient extends Model
{
    protected $fillable = [
        'campaign_id',
        'email',
        'name',
        'contact_id',
        'lead_id',
        'status',
        'sent_at',
        'opened_at',
        'clicked_at',
        'tracking_token',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (EmailCampaignRecipient $recipient) {
            if (empty($recipient->tracking_token)) {
                $recipient->tracking_token = Str::random(40);
            }
        });
    }

    public function campaign()
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }

    public function contact()
    {
        return $this->belongsTo(ClientContact::class, 'contact_id');
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function markSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markOpened(): void
    {
        if ($this->status === 'sent') {
            $this->update([
                'status' => 'opened',
                'opened_at' => now(),
            ]);
        }
    }

    public function markClicked(): void
    {
        $this->update([
            'status' => 'clicked',
            'clicked_at' => now(),
        ]);
    }

    public function markBounced(): void
    {
        $this->update(['status' => 'bounced']);
    }

    public function markUnsubscribed(): void
    {
        $this->update(['status' => 'unsubscribed']);
    }
}
