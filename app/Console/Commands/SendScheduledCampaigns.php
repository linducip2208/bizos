<?php

namespace App\Console\Commands;

use App\Models\EmailCampaign;
use App\Services\EmailCampaignService;
use Illuminate\Console\Command;

class SendScheduledCampaigns extends Command
{
    protected $signature = 'marketing:send-scheduled';

    protected $description = 'Send scheduled email campaigns whose scheduled_at is now or earlier';

    public function handle(): int
    {
        $service = app(EmailCampaignService::class);

        $campaigns = EmailCampaign::where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($campaigns->isEmpty()) {
            $this->info('No scheduled campaigns to send.');

            return Command::SUCCESS;
        }

        foreach ($campaigns as $campaign) {
            $this->info("Sending campaign: {$campaign->name} (ID: {$campaign->id})");

            try {
                $service->sendCampaign($campaign);
                $this->info("  Done: {$campaign->sent_count} emails sent.");
            } catch (\Exception $e) {
                $this->error("  Failed: {$e->getMessage()}");
            }
        }

        return Command::SUCCESS;
    }
}
