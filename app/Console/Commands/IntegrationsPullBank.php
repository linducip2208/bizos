<?php

namespace App\Console\Commands;

use App\Models\BankAccount;
use App\Services\IntegrationHubService;
use Illuminate\Console\Command;

class IntegrationsPullBank extends Command
{
    protected $signature = 'integrations:pull-bank {--bank= : Nama bank (bca, mandiri, bri, bni, cimb)}';

    protected $description = 'Tarik data transaksi bank harian';

    public function handle(IntegrationHubService $hub): int
    {
        $this->info('Menarik data transaksi bank...');

        $query = BankAccount::where('is_active', true);
        if ($bank = $this->option('bank')) {
            $query->where('bank_name', 'like', "%{$bank}%");
        }

        $accounts = $query->get();

        $this->info("Ditemukan {$accounts->count()} rekening bank aktif.");

        $bar = $this->output->createProgressBar($accounts->count());
        $bar->start();

        foreach ($accounts as $account) {
            try {
                $hub->fetchBankTransactions($account->id, now()->subDay(), now());
            } catch (\Exception $e) {
                $this->warn("\n[{$account->bank_name} - {$account->account_number}] Error: " . $e->getMessage());
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Pull transaksi bank selesai.');

        return self::SUCCESS;
    }
}
