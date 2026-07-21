<?php

namespace App\Console\Commands;

use App\Services\ApprovalWorkflowService;
use Illuminate\Console\Command;

class CheckApprovalSla extends Command
{
    protected $signature = 'approval:check-sla';

    protected $description = 'Check all pending approvals for SLA breaches and take configured actions';

    public function handle(ApprovalWorkflowService $service): int
    {
        $this->info('Checking approval SLAs...');

        $service->checkSlaBreaches();

        $this->info('SLA check completed.');

        return self::SUCCESS;
    }
}
