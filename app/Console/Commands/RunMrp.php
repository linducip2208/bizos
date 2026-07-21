<?php

namespace App\Console\Commands;

use App\Services\MrpService;
use Illuminate\Console\Command;

class RunMrp extends Command
{
    protected $signature = 'manufacturing:run-mrp {--company= : Company ID} {--days=30 : Horizon days}';
    protected $description = 'Menjalankan perhitungan MRP harian untuk semua produk';

    public function handle(MrpService $mrpService): int
    {
        $companyId = $this->option('company');
        $days = (int) $this->option('days');

        if ($companyId) {
            $this->info("Menjalankan MRP untuk company #{$companyId}...");
            $result = $mrpService->runFullMrp((int) $companyId, $days);
        } else {
            $this->info("Menjalankan MRP untuk semua company...");
            $companies = \App\Models\Company::all();
            $result = [];

            foreach ($companies as $company) {
                $this->line("  - Company: {$company->name}");
                $result[$company->id] = $mrpService->runFullMrp($company->id, $days);
            }
        }

        $totalProducts = 0;
        $totalShortages = 0;

        if (isset($result['products_analyzed'])) {
            $totalProducts = $result['products_analyzed'];
            $totalShortages = $result['products_with_shortage'];
        } else {
            foreach ($result as $r) {
                $totalProducts += $r['products_analyzed'] ?? 0;
                $totalShortages += $r['products_with_shortage'] ?? 0;
            }
        }

        $this->info("MRP selesai. {$totalProducts} produk dianalisis, {$totalShortages} shortage terdeteksi.");

        if ($totalShortages > 0) {
            $this->warn("Gunakan 'manufacturing:daily-oee' atau cek MRP Dashboard untuk detail.");
        }

        return self::SUCCESS;
    }
}
