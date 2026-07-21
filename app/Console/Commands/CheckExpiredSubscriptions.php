<?php

namespace App\Console\Commands;

use App\Services\BillingService;
use Illuminate\Console\Command;

class CheckExpiredSubscriptions extends Command
{
    protected $signature = 'billing:check-expired';

    protected $description = 'Check and mark expired subscriptions.';

    public function handle(BillingService $billingService): int
    {
        $this->info('Checking for expired subscriptions...');

        $count = $billingService->checkAndExpireSubscriptions();

        $this->info("{$count} subscription(s) updated.");

        return self::SUCCESS;
    }
}
