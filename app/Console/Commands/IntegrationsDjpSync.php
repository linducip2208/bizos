<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\IntegrationHubService;
use Illuminate\Console\Command;

class IntegrationsDjpSync extends Command
{
    protected $signature = 'integrations:djp-sync {--company= : ID perusahaan spesifik} {--type= : efaktur, pkpm, atau all}';

    protected $description = 'Sinkronisasi data DJP (e-Faktur, PK/PM) harian';

    public function handle(IntegrationHubService $hub): int
    {
        $this->info('Memulai sinkronisasi DJP...');

        $companies = $this->option('company')
            ? Company::where('id', $this->option('company'))->get()
            : Company::where('is_active', true)->get();

        $type = $this->option('type') ?? 'all';

        foreach ($companies as $company) {
            $this->line("[{$company->name}] Sinkronisasi DJP...");

            try {
                if ($type === 'all' || $type === 'efaktur') {
                    // Pull PK data for current period
                    $period = now()->format('Y-m');
                    $result = $hub->pullPajakMasukan($company->id, $period);
                    $this->line("  PK/PM: " . ($result['success'] ? 'OK' : 'Gagal - ' . ($result['message'] ?? '-')));
                }

                if ($type === 'all' || $type === 'spt') {
                    // Submit SPT if due (monthly)
                    if (now()->day >= 20 && now()->day <= 30) {
                        $this->line("  SPT: Periode pengumpulan SPT Masa (tanggal 20-30)");
                    }
                }
            } catch (\Exception $e) {
                $this->warn("[{$company->name}] Error: " . $e->getMessage());
            }
        }

        $this->info('Sinkronisasi DJP selesai.');

        return self::SUCCESS;
    }
}
