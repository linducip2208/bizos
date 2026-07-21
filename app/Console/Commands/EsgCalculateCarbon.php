<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\EsgService;
use Illuminate\Console\Command;

class EsgCalculateCarbon extends Command
{
    protected $signature = 'esg:calculate-carbon {--company= : ID perusahaan (kosongkan untuk semua)} {--period= : Periode Y-m (default: bulan ini)}';

    protected $description = 'Hitung jejak karbon bulanan untuk semua perusahaan';

    public function handle(EsgService $esgService): int
    {
        $period = $this->option('period') ?? now()->format('Y-m');

        $companies = $this->option('company')
            ? Company::where('id', $this->option('company'))->get()
            : Company::where('is_active', true)->get();

        $this->info("Menghitung jejak karbon untuk periode: {$period}");
        $this->info("Jumlah perusahaan: " . $companies->count());

        $bar = $this->output->createProgressBar($companies->count());
        $bar->start();

        foreach ($companies as $company) {
            try {
                $this->line("\n[{$company->name}] Menghitung...");

                $scope1 = $esgService->calculateScope1($company->id, $period);
                $this->line("  Scope 1: {$scope1['total_tco2e']} tCO2e");

                $scope2 = $esgService->calculateScope2($company->id, $period);
                $this->line("  Scope 2: {$scope2['total_tco2e']} tCO2e");

                $scope3 = $esgService->calculateScope3($company->id, $period);
                $this->line("  Scope 3: {$scope3['total_tco2e']} tCO2e");

                $total = $esgService->getTotalCarbonFootprint($company->id, $period);
                $this->line("  TOTAL: {$total['total_tco2e']} tCO2e");

            } catch (\Exception $e) {
                $this->error("[{$company->name}] Error: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Perhitungan jejak karbon selesai.');

        return self::SUCCESS;
    }
}
