<?php

namespace App\Console\Commands;

use App\Services\BillingService;
use Illuminate\Console\Command;

class GenerateSubscriptionInvoices extends Command
{
    protected $signature = 'billing:generate-invoices';

    protected $description = 'Generate invoices for active subscriptions near period end.';

    public function handle(BillingService $billingService): int
    {
        $this->info('Generating subscription invoices...');

        $count = $billingService->generateDueInvoices();
        $this->info("{$count} invoice(s) generated.");

        $overdueCount = $billingService->checkOverdueInvoices();
        if ($overdueCount > 0) {
            $this->warn("{$overdueCount} invoice(s) marked as overdue.");
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}
