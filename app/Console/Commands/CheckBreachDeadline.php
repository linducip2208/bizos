<?php

namespace App\Console\Commands;

use App\Models\DataBreach;
use App\Services\PdpComplianceService;
use Illuminate\Console\Command;

class CheckBreachDeadline extends Command
{
    protected $signature = 'compliance:check-breach-deadline';
    protected $description = 'Periksa pelanggaran data yang mendekati atau melebihi batas notifikasi 72 jam';

    public function handle(): int
    {
        $this->info('Memeriksa batas waktu notifikasi pelanggaran data...');

        $service = app(PdpComplianceService::class);

        $openBreaches = DataBreach::whereNotIn('status', ['resolved', 'closed'])
            ->whereNull('notified_dpa_at')
            ->get();

        $alerts = [];

        foreach ($openBreaches as $breach) {
            $hoursSinceDiscovery = $breach->discovered_at->diffInHours(now());
            $deadline = $breach->discovered_at->copy()->addHours(72);
            $hoursRemaining = max(0, 72 - $hoursSinceDiscovery);

            if ($hoursSinceDiscovery > 72) {
                $alerts[] = [
                    'ID' => $breach->id,
                    'Tipe' => $breach->breach_type,
                    'Keparahan' => $breach->severity,
                    'Ditemukan' => $breach->discovered_at->format('d M Y H:i'),
                    'Status' => 'TERLAMBAT',
                    'Jam Berlalu' => round($hoursSinceDiscovery, 1) . ' jam',
                    'Deskripsi' => \Illuminate\Support\Str::limit($breach->description, 50),
                ];

                $this->warn("⚠ BREACH #{$breach->id} TERLAMBAT: {$breach->discovered_at->diffInHours(now())} jam sejak ditemukan!");
            } elseif ($hoursRemaining <= 12 && $hoursRemaining > 0) {
                $alerts[] = [
                    'ID' => $breach->id,
                    'Tipe' => $breach->breach_type,
                    'Keparahan' => $breach->severity,
                    'Ditemukan' => $breach->discovered_at->format('d M Y H:i'),
                    'Status' => 'MENDESAK',
                    'Sisa Waktu' => round($hoursRemaining, 1) . ' jam',
                    'Deskripsi' => \Illuminate\Support\Str::limit($breach->description, 50),
                ];

                $this->warn("⚠ BREACH #{$breach->id} MENDESAK: {$hoursRemaining} jam tersisa untuk notifikasi ke Kominfo!");
            } elseif ($hoursRemaining <= 24 && $hoursRemaining > 0) {
                $alerts[] = [
                    'ID' => $breach->id,
                    'Tipe' => $breach->breach_type,
                    'Keparahan' => $breach->severity,
                    'Ditemukan' => $breach->discovered_at->format('d M Y H:i'),
                    'Status' => 'PERHATIAN',
                    'Sisa Waktu' => round($hoursRemaining, 1) . ' jam',
                    'Deskripsi' => \Illuminate\Support\Str::limit($breach->description, 50),
                ];
            }
        }

        if (empty($alerts)) {
            $this->info('Semua pelanggaran data dalam batas waktu notifikasi 72 jam.');
        } else {
            $this->table(
                ['ID', 'Tipe', 'Keparahan', 'Ditemukan', 'Status', 'Waktu', 'Deskripsi'],
                $alerts
            );
        }

        return self::SUCCESS;
    }
}
