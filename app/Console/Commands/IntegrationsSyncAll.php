<?php

namespace App\Console\Commands;

use App\Services\IntegrationHubService;
use Illuminate\Console\Command;

class IntegrationsSyncAll extends Command
{
    protected $signature = 'integrations:sync-all {--force : Paksa sync semua walau belum jadwalnya}';

    protected $description = 'Jalankan semua sinkronisasi integrasi yang terjadwal';

    public function handle(IntegrationHubService $hub): int
    {
        $this->info('Memulai sinkronisasi semua integrasi...');

        $results = $hub->runAllScheduledSyncs();

        $successCount = 0;
        $failCount = 0;

        foreach ($results as $result) {
            $entity = $result['entity'] ?? 'unknown';
            $connector = $result['connector'] ?? 'unknown';

            if ($result['success'] ?? false) {
                $this->info("  [OK] {$connector}:{$entity}");
                $successCount++;
            } else {
                $this->warn("  [FAIL] {$connector}:{$entity} — " . ($result['message'] ?? $result['error'] ?? '-'));
                $failCount++;
            }
        }

        $this->info("Selesai. Berhasil: {$successCount}, Gagal: {$failCount}");

        return self::SUCCESS;
    }
}
