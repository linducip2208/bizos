<?php

namespace App\Console\Commands;

use App\Services\TenantUsageService;
use Illuminate\Console\Command;

class RecordTenantUsage extends Command
{
    protected $signature = 'tenant:record-usage';
    protected $description = 'Catat penggunaan harian semua tenant';

    public function handle(TenantUsageService $usageService): int
    {
        $this->info('Mencatat penggunaan tenant...');

        $usageService->recordAllCompaniesUsage();

        $this->info('Selesai mencatat penggunaan tenant.');
        return self::SUCCESS;
    }
}
