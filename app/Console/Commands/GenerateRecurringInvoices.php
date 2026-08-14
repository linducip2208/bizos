<?php

namespace App\Console\Commands;

use App\Services\RecurringBillingService;
use Illuminate\Console\Command;

class GenerateRecurringInvoices extends Command
{
    protected $signature = 'billing:generate-recurring';

    protected $description = 'Generate invoices for recurring invoice schedules that are due.';

    public function handle(RecurringBillingService $service): int
    {
        $this->info('Generating recurring invoices...');

        $count = $service->runScheduledGeneration();

        $this->info("{$count} invoice(s) generated.");

        return self::SUCCESS;
    }
}
