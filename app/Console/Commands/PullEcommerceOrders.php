<?php

namespace App\Console\Commands;

use App\Models\EcommerceChannel;
use App\Services\EcommerceService;
use Illuminate\Console\Command;

class PullEcommerceOrders extends Command
{
    protected $signature = 'ecommerce:pull-orders {--channel= : ID channel spesifik} {--since= : Dari tanggal (Y-m-d H:i)}';
    protected $description = 'Tarik pesanan baru dari semua channel e-commerce yang aktif';

    public function handle(EcommerceService $service): void
    {
        $channels = $this->option('channel')
            ? EcommerceChannel::where('id', $this->option('channel'))->get()
            : EcommerceChannel::where('is_active', true)->get();

        if ($channels->isEmpty()) {
            $this->warn('Tidak ada channel e-commerce yang aktif.');
            return;
        }

        $since = $this->option('since')
            ? \Carbon\Carbon::parse($this->option('since'))
            : now()->subDay();

        $this->info("Menarik pesanan sejak: {$since->format('Y-m-d H:i:s')}");

        $totalAll = ['new_orders' => 0, 'synced_count' => 0, 'failed_count' => 0];

        foreach ($channels as $channel) {
            $this->line("Memproses channel: {$channel->channel_name}...");

            $result = $service->pullOrders($channel, $since);

            $this->table(
                ['Metrik', 'Jumlah'],
                [
                    ['Pesanan Baru', $result['new_orders']],
                    ['Update', $result['synced_count']],
                    ['Gagal', $result['failed_count']],
                ]
            );

            $totalAll['new_orders'] += $result['new_orders'];
            $totalAll['synced_count'] += $result['synced_count'];
            $totalAll['failed_count'] += $result['failed_count'];
        }

        $this->info("Total: {$totalAll['new_orders']} baru, {$totalAll['synced_count']} update, {$totalAll['failed_count']} gagal");
    }
}
