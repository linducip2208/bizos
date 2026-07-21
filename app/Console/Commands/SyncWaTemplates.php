<?php

namespace App\Console\Commands;

use App\Services\WhatsappBusinessService;
use Illuminate\Console\Command;

class SyncWaTemplates extends Command
{
    protected $signature = 'wa:sync-templates';
    protected $description = 'Sinkronisasi template WhatsApp dari Meta Business API';

    public function handle(WhatsappBusinessService $waService): int
    {
        $this->info('Menyinkronkan template WA dari Meta...');

        $result = $waService->syncTemplates();

        if ($result['success']) {
            $this->info("Berhasil: {$result['synced']} template disinkronkan");
            return self::SUCCESS;
        }

        $this->error("Gagal: {$result['message']}");
        return self::FAILURE;
    }
}
