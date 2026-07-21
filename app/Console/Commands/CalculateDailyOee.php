<?php

namespace App\Console\Commands;

use App\Models\WorkCenter;
use App\Services\ManufacturingService;
use Illuminate\Console\Command;

class CalculateDailyOee extends Command
{
    protected $signature = 'manufacturing:daily-oee {--company= : Company ID}';
    protected $description = 'Menghitung OEE harian untuk semua work center';

    public function handle(ManufacturingService $manufacturing): int
    {
        $this->info('Menghitung OEE untuk semua work center...');

        $query = WorkCenter::where('is_active', true)->with('company');

        if ($companyId = $this->option('company')) {
            $query->where('company_id', (int) $companyId);
        }

        $workCenters = $query->get();
        $bar = $this->output->createProgressBar($workCenters->count());
        $bar->start();

        $results = [];
        foreach ($workCenters as $wc) {
            $oee = $manufacturing->calculateOee($wc, 'daily');
            $results[] = array_merge(['company' => $wc->company->name ?? 'N/A'], $oee);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->table(
            ['Perusahaan', 'Work Center', 'Availability%', 'Performance%', 'Quality%', 'OEE%'],
            collect($results)->map(fn($r) => [
                $r['company'],
                $r['work_center'],
                $r['availability_percent'],
                $r['performance_percent'],
                $r['quality_percent'],
                $r['oee_percent'],
            ])->toArray()
        );

        $lowOee = collect($results)->filter(fn($r) => $r['oee_percent'] < 60);
        if ($lowOee->isNotEmpty()) {
            $this->warn($lowOee->count() . ' work center memiliki OEE di bawah 60%. Perlu tindakan korektif.');
        }

        return self::SUCCESS;
    }
}
