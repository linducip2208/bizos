<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\TenantService;
use App\Services\TenantUsageService;
use Illuminate\Console\Command;

class EnforceTenantLimits extends Command
{
    protected $signature = 'tenant:enforce-limits';
    protected $description = 'Periksa dan tegakkan batas penggunaan tenant. Suspend tenant yang melebihi batas.';

    public function handle(TenantService $tenantService, TenantUsageService $usageService): int
    {
        $this->info('Memulai pemeriksaan batas tenant...');

        $companies = Company::where('is_active', true)
            ->where('is_suspended', false)
            ->get();

        $checked = 0;
        $suspended = 0;
        $usageRecorded = 0;

        foreach ($companies as $company) {
            $checked++;

            // Record daily usage
            try {
                $usageService->recordCompanyUsage($company->id);
                $usageRecorded++;
            } catch (\Exception $e) {
                $this->warn("Gagal record usage untuk {$company->name}: {$e->getMessage()}");
            }

            // Enforce limits
            try {
                $wasSuspended = $company->is_suspended;
                $tenantService->enforceLimits($company);

                if (!$wasSuspended && $company->fresh()->is_suspended) {
                    $suspended++;
                    $this->warn("Tenant DISUSPEND: {$company->name} — {$company->fresh()->suspended_reason}");
                }
            } catch (\Exception $e) {
                $this->error("Gagal enforce limits untuk {$company->name}: {$e->getMessage()}");
            }
        }

        // Purge expired data
        try {
            $purged = $tenantService->purgeExpiredTenantData();
            if ($purged > 0) {
                $this->info("Data tenant kadaluarsa dibersihkan: {$purged} tenant");
            }
        } catch (\Exception $e) {
            $this->error("Gagal purge data: {$e->getMessage()}");
        }

        $this->info("Selesai: {$checked} dicek, {$suspended} disuspend, {$usageRecorded} usage tercatat.");

        return self::SUCCESS;
    }
}
