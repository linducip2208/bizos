<?php

namespace App\Console\Commands;

use App\Services\FieldServiceService;
use Illuminate\Console\Command;

class FieldServiceCheckVanStock extends Command
{
    protected $signature = 'fieldservice:check-van-stock';
    protected $description = 'Cek stok van teknisi dan laporkan yang di bawah reorder point';

    public function handle(FieldServiceService $service): int
    {
        $this->info('Memeriksa stok van teknisi...');

        $results = $service->checkVanStock();

        if (empty($results['alerts'])) {
            $this->info('Semua stok van aman.');
            return self::SUCCESS;
        }

        $this->warn("Ditemukan " . count($results['alerts']) . " item di bawah reorder point:");

        foreach ($results['alerts'] as $alert) {
            $this->line("  - [{$alert['license_plate']}] {$alert['technician']}: {$alert['product']} ({$alert['current_qty']} < {$alert['reorder_point']})");
        }

        $this->info("Stok OK: {$results['ok']} item");

        return self::SUCCESS;
    }
}
