<?php

namespace App\Console\Commands;

use App\Services\FieldServiceService;
use Illuminate\Console\Command;

class FieldServiceGenerateWorkOrders extends Command
{
    protected $signature = 'fieldservice:generate-work-orders';
    protected $description = 'Auto-generate work orders dari service contracts yang jatuh tempo';

    public function handle(FieldServiceService $service): int
    {
        $this->info('Memproses service contracts...');

        $results = $service->generateScheduledWorkOrders();

        $this->info("Dibuat: {$results['created']}");
        $this->info("Dilewati: {$results['skipped']}");
        if ($results['errors'] > 0) {
            $this->error("Error: {$results['errors']}");
        }

        return self::SUCCESS;
    }
}
