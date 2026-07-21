<?php

namespace App\Console\Commands;

use App\Models\EcommerceChannel;
use App\Services\EcommerceService;
use Illuminate\Console\Command;

class PushEcommerceInventory extends Command
{
    protected $signature = 'ecommerce:push-inventory {--channel= : ID channel spesifik}';
    protected $description = 'Dorong update stok ke semua channel e-commerce yang aktif';

    public function handle(EcommerceService $service): void
    {
        $channels = $this->option('channel')
            ? EcommerceChannel::where('id', $this->option('channel'))->get()
            : EcommerceChannel::where('is_active', true)->get();

        if ($channels->isEmpty()) {
            $this->warn('Tidak ada channel e-commerce yang aktif.');
            return;
        }

        foreach ($channels as $channel) {
            $this->line("Mendorong inventory ke: {$channel->channel_name}...");
            $service->pushInventory($channel);
        }

        $this->info('Push inventory selesai.');
    }
}
