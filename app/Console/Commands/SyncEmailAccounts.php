<?php

namespace App\Console\Commands;

use App\Services\EmailClientService;
use Illuminate\Console\Command;

class SyncEmailAccounts extends Command
{
    protected $signature = 'email:sync-accounts {--account-id= : Specific account ID to sync}';

    protected $description = 'Sinkronisasi email dari semua akun IMAP yang terhubung';

    public function handle(EmailClientService $service): int
    {
        $accountId = $this->option('account-id');

        if ($accountId) {
            $account = \App\Models\EmailAccount::find($accountId);
            if (!$account) {
                $this->error("Akun email #{$accountId} tidak ditemukan.");
                return self::FAILURE;
            }

            $this->info("Sinkronisasi akun: {$account->email}...");
            $synced = $service->syncAccount($account);
            $this->info("{$synced} email baru disinkronisasi.");
            return self::SUCCESS;
        }

        $this->info('Memulai sinkronisasi semua akun email...');
        $start = microtime(true);
        $service->syncAllAccounts();
        $elapsed = round(microtime(true) - $start, 2);
        $this->info("Selesai dalam {$elapsed} detik.");

        return self::SUCCESS;
    }
}
