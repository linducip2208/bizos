<?php

namespace App\Services;

use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use App\Models\Lead;
use App\Models\LeadActivityLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailCampaignService
{
    public function createCampaign(array $data): EmailCampaign
    {
        $campaign = EmailCampaign::create([
            'company_id' => $data['company_id'] ?? auth()->user()->company_id,
            'name' => $data['name'],
            'subject' => $data['subject'],
            'sender_name' => $data['sender_name'] ?? config('app.name'),
            'sender_email' => $data['sender_email'] ?? config('mail.from.address'),
            'template_content' => $data['template_content'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'created_by' => $data['created_by'] ?? auth()->user()?->employee?->id,
        ]);

        if (!empty($data['recipients'])) {
            $this->addRecipients($campaign, $data['recipients']);
        }

        return $campaign;
    }

    public function addRecipients(EmailCampaign $campaign, array $recipients): void
    {
        $count = 0;
        foreach ($recipients as $recipient) {
            EmailCampaignRecipient::create([
                'campaign_id' => $campaign->id,
                'email' => $recipient['email'],
                'name' => $recipient['name'] ?? null,
                'contact_id' => $recipient['contact_id'] ?? null,
                'lead_id' => $recipient['lead_id'] ?? null,
            ]);
            $count++;
        }

        $campaign->update(['total_recipients' => $campaign->total_recipients + $count]);
    }

    public function sendCampaign(EmailCampaign $campaign): void
    {
        if (!in_array($campaign->status, ['draft', 'scheduled'])) {
            return;
        }

        $campaign->update(['status' => 'sending']);

        $recipients = $campaign->recipients()->where('status', 'pending')->get();

        if ($recipients->isEmpty()) {
            $campaign->update(['status' => 'draft']);
            return;
        }

        $baseUrl = rtrim(config('app.url'), '/');

        foreach ($recipients as $recipient) {
            try {
                $trackingToken = $recipient->tracking_token;
                $openTrackingUrl = $baseUrl . '/email/track/open/' . $trackingToken;
                $clickTrackingUrl = $baseUrl . '/email/track/click/' . $trackingToken;

                $body = $this->injectTrackingPixel(
                    $this->processTemplateUrls($campaign->template_content, $clickTrackingUrl),
                    $openTrackingUrl
                );

                Mail::html($body, function ($message) use ($campaign, $recipient) {
                    $message->to($recipient->email, $recipient->name)
                        ->from($campaign->sender_email, $campaign->sender_name)
                        ->subject($campaign->subject);
                });

                $recipient->markSent();
                $campaign->increment('sent_count');
            } catch (\Exception $e) {
                Log::error("Failed to send campaign email: {$e->getMessage()}", [
                    'campaign_id' => $campaign->id,
                    'recipient_id' => $recipient->id,
                ]);
                $recipient->markBounced();
                $campaign->increment('bounced_count');
            }
        }

        $campaign->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function sendTestEmail(EmailCampaign $campaign, string $email): void
    {
        $body = $this->processTemplateUrls($campaign->template_content, '#');
        $body = str_replace('{{tracking_pixel}}', '', $body);

        Mail::html($body, function ($message) use ($campaign, $email) {
            $message->to($email)
                ->from($campaign->sender_email, $campaign->sender_name)
                ->subject('[TEST] ' . $campaign->subject);
        });
    }

    public function trackOpen(string $token): void
    {
        $recipient = EmailCampaignRecipient::where('tracking_token', $token)->first();

        if (!$recipient || $recipient->status === 'bounced' || $recipient->status === 'unsubscribed') {
            return;
        }

        $previouslyOpened = $recipient->status === 'opened';

        $recipient->markOpened();

        if (!$previouslyOpened) {
            $recipient->campaign->trackOpens();

            if ($recipient->lead_id) {
                LeadActivityLog::create([
                    'lead_id' => $recipient->lead_id,
                    'activity_type' => 'email_opened',
                    'metadata' => [
                        'campaign_id' => $recipient->campaign_id,
                        'campaign_name' => $recipient->campaign->name,
                        'recipient_email' => $recipient->email,
                    ],
                    'created_at' => now(),
                ]);
            }
        }
    }

    public function trackClick(string $token, string $url): void
    {
        $recipient = EmailCampaignRecipient::where('tracking_token', $token)->first();

        if (!$recipient || $recipient->status === 'bounced' || $recipient->status === 'unsubscribed') {
            return;
        }

        $previouslyClicked = $recipient->status === 'clicked';

        $recipient->markClicked();

        if (!$previouslyClicked) {
            $recipient->campaign->trackClicks();

            if ($recipient->lead_id) {
                LeadActivityLog::create([
                    'lead_id' => $recipient->lead_id,
                    'activity_type' => 'email_clicked',
                    'metadata' => [
                        'campaign_id' => $recipient->campaign_id,
                        'campaign_name' => $recipient->campaign->name,
                        'clicked_url' => $url,
                    ],
                    'created_at' => now(),
                ]);
            }
        }
    }

    public function getStats(EmailCampaign $campaign): array
    {
        $campaign->refresh();

        return [
            'total_recipients' => $campaign->total_recipients,
            'sent_count' => $campaign->sent_count,
            'opened_count' => $campaign->opened_count,
            'clicked_count' => $campaign->clicked_count,
            'bounced_count' => $campaign->bounced_count,
            'unsubscribed_count' => $campaign->unsubscribed_count,
            'open_rate' => $campaign->open_rate,
            'click_rate' => $campaign->click_rate,
            'delivery_rate' => $campaign->total_recipients > 0
                ? round((($campaign->sent_count) / $campaign->total_recipients) * 100, 2)
                : 0,
        ];
    }

    protected function injectTrackingPixel(string $html, string $trackingUrl): string
    {
        $pixel = '<img src="' . e($trackingUrl) . '" width="1" height="1" alt="" style="display:none;" />';

        if (str_contains($html, '<body')) {
            return preg_replace('/(<body[^>]*>)/i', '$1' . $pixel, $html);
        }

        return $html . $pixel;
    }

    protected function processTemplateUrls(string $html, string $clickTrackingBase): string
    {
        return preg_replace_callback(
            '/<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>/i',
            function ($matches) use ($clickTrackingBase) {
                $originalUrl = $matches[1];
                if (str_starts_with($originalUrl, 'mailto:') || str_starts_with($originalUrl, '#')) {
                    return $matches[0];
                }
                $trackedUrl = $clickTrackingBase . '?url=' . urlencode($originalUrl);
                return str_replace($originalUrl, $trackedUrl, $matches[0]);
            },
            $html
        );
    }
}
