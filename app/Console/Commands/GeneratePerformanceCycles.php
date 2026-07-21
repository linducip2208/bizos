<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\PerformanceCycle;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GeneratePerformanceCycles extends Command
{
    protected $signature = 'performance:generate-cycles {--year= : Tahun yang akan digenerate (default: tahun ini)}';
    protected $description = 'Auto-generate siklus performa kuartalan dan tahunan untuk semua perusahaan';

    public function handle(): int
    {
        $year = $this->option('year') ?? now()->year;

        $companies = Company::where('is_active', true)->get();

        if ($companies->isEmpty()) {
            $this->warn('Tidak ada perusahaan aktif.');
            return self::SUCCESS;
        }

        $created = 0;

        foreach ($companies as $company) {
            $cycles = [
                [
                    'name' => "Q1 {$year}",
                    'period_start' => Carbon::create($year, 1, 1)->startOfMonth(),
                    'period_end' => Carbon::create($year, 3, 31)->endOfMonth(),
                ],
                [
                    'name' => "Q2 {$year}",
                    'period_start' => Carbon::create($year, 4, 1)->startOfMonth(),
                    'period_end' => Carbon::create($year, 6, 30)->endOfMonth(),
                ],
                [
                    'name' => "Q3 {$year}",
                    'period_start' => Carbon::create($year, 7, 1)->startOfMonth(),
                    'period_end' => Carbon::create($year, 9, 30)->endOfMonth(),
                ],
                [
                    'name' => "Q4 {$year}",
                    'period_start' => Carbon::create($year, 10, 1)->startOfMonth(),
                    'period_end' => Carbon::create($year, 12, 31)->endOfMonth(),
                ],
                [
                    'name' => "Annual Review {$year}",
                    'period_start' => Carbon::create($year, 1, 1)->startOfMonth(),
                    'period_end' => Carbon::create($year, 12, 31)->endOfMonth(),
                ],
            ];

            foreach ($cycles as $cycle) {
                $exists = PerformanceCycle::where('company_id', $company->id)
                    ->where('name', $cycle['name'])
                    ->exists();

                if ($exists) {
                    $this->line("  ⏭ {$company->name} — {$cycle['name']} (sudah ada)");
                    continue;
                }

                PerformanceCycle::create([
                    'company_id' => $company->id,
                    'name' => $cycle['name'],
                    'period_start' => $cycle['period_start'],
                    'period_end' => $cycle['period_end'],
                    'status' => 'draft',
                ]);

                $created++;
                $this->line("  ✅ {$company->name} — {$cycle['name']}");
            }
        }

        $this->newLine();
        $this->info("{$created} siklus performa berhasil dibuat untuk tahun {$year}.");

        return self::SUCCESS;
    }
}
