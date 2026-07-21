<?php

namespace App\Console\Commands;

use App\Services\WebhookService;
use Illuminate\Console\Command;

class RetryFailedWebhooks extends Command
{
    protected $signature = 'webhook:retry-failed';

    protected $description = 'Retry failed webhook deliveries with exponential backoff.';

    public function handle(): int
    {
        $webhookService = app(WebhookService::class);

        $this->info('Mencoba ulang webhook yang gagal...');
        $webhookService->retryFailed();
        $this->info('Selesai.');

        return self::SUCCESS;
    }
}
