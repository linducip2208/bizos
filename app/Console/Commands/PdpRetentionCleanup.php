<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Services\PdpComplianceService;
use Illuminate\Console\Command;

class PdpRetentionCleanup extends Command
{
    protected $signature = 'compliance:retention-cleanup';
    protected $description = 'Auto-delete/anonymize data yang melebihi masa retensi sesuai kebijakan PDP';

    public function handle(): int
    {
        $this->info('Memulai pembersihan data retensi PDP...');

        $service = app(PdpComplianceService::class);
        $service->applyRetentionPolicy();

        $retention = $service->getRetentionStatus();

        $this->table(
            ['Metrik', 'Nilai'],
            [
                ['Total Karyawan', $retention['total_employees']],
                ['Karyawan Aktif', $retention['active_employees']],
                ['Data Dianonimkan', $retention['anonymized_records']],
                ['Karyawan Terminasi', $retention['terminated_records']],
                ['Pending Erasure', $retention['pending_erasure_requests']],
                ['Status Kepatuhan', $retention['policy_compliance']],
            ]
        );

        $this->info('Pembersihan retensi selesai.');
        return self::SUCCESS;
    }
}
