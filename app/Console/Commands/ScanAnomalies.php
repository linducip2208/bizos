<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\User;
use App\Services\AnomalyDetectionService;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class ScanAnomalies extends Command
{
    protected $signature = 'bizos:scan-anomalies {--company= : ID perusahaan spesifik} {--send-wa : Kirim laporan via WhatsApp ke admin}';

    protected $description = 'Jalankan pemindaian anomali di semua modul (Payroll, Finance, Inventory, Attendance)';

    public function handle(): int
    {
        $service = app(AnomalyDetectionService::class);
        $sendWa = $this->option('send-wa');

        $companyIds = [];
        if ($this->option('company')) {
            $companyIds = [(int) $this->option('company')];
        } else {
            $companyIds = Company::where('is_active', true)->pluck('id')->toArray();
        }

        $totalAnomalies = 0;

        foreach ($companyIds as $companyId) {
            $this->info("Memindai anomali untuk perusahaan ID: {$companyId}");

            try {
                $anomalies = $service->scanAll($companyId);
                $count = count($anomalies);

                $highCount = count(array_filter($anomalies, fn($a) => ($a['severity'] ?? '') === 'high'));
                $mediumCount = count(array_filter($anomalies, fn($a) => ($a['severity'] ?? '') === 'medium'));
                $lowCount = count(array_filter($anomalies, fn($a) => ($a['severity'] ?? '') === 'low'));

                $this->info("  Total: {$count} anomali (High: {$highCount}, Medium: {$mediumCount}, Low: {$lowCount})");

                foreach ($anomalies as $anomaly) {
                    $severityIcon = match ($anomaly['severity']) {
                        'high' => '   ! [HIGH]',
                        'medium' => '   ~ [MED] ',
                        default => '   - [LOW] ',
                    };
                    $this->line("{$severityIcon} {$anomaly['title']}");
                }

                if ($sendWa && $count > 0) {
                    $report = $service->generateWeeklyReport($companyId);

                    $adminUsers = User::where('company_id', $companyId)
                        ->whereHas('role', fn($q) => $q->whereIn('slug', ['super-admin', 'admin', 'owner']))
                        ->get();

                    foreach ($adminUsers as $user) {
                        NotificationService::send(
                            $user->id,
                            'anomaly_report',
                            'Laporan Anomali Mingguan',
                            $report,
                            'whatsapp',
                            ['company_id' => $companyId]
                        );
                    }
                }

                $totalAnomalies += $count;
            } catch (\Exception $e) {
                $this->error("  Gagal scan perusahaan ID {$companyId}: " . $e->getMessage());
            }
        }

        $this->info("Scan selesai. Total anomali ditemukan: {$totalAnomalies}");

        return self::SUCCESS;
    }
}
