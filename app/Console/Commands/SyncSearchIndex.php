<?php

namespace App\Console\Commands;

use App\Services\EnterpriseSearchService;
use Illuminate\Console\Command;

class SyncSearchIndex extends Command
{
    protected $signature = 'search:sync-index
                            {--model= : Specific model class to sync (e.g. App\\Models\\Employee)}
                            {--setup : Also run setupIndexes before syncing}';

    protected $description = 'Sinkronisasi semua model ke Meilisearch index';

    public function handle(EnterpriseSearchService $service): int
    {
        if (!$service->isAvailable()) {
            $this->error('Meilisearch tidak tersedia. Pastikan server Meilisearch berjalan.');
            $this->info('Host: ' . config('services.meilisearch.host', 'http://localhost:7700'));
            return self::FAILURE;
        }

        if ($this->option('setup')) {
            $this->info('Menginisialisasi semua index...');
            $service->setupIndexes();
            $this->info('Index setup selesai.');
        }

        $model = $this->option('model');

        if ($model) {
            if (!class_exists($model)) {
                $this->error("Model class '{$model}' tidak ditemukan.");
                return self::FAILURE;
            }
            $this->info("Sinkronisasi model: {$model}...");
            $start = microtime(true);
            $service->syncModel($model);
            $elapsed = round(microtime(true) - $start, 2);
            $this->info("Selesai dalam {$elapsed} detik.");
            return self::SUCCESS;
        }

        $this->info('Memulai sinkronisasi penuh semua model...');
        $start = microtime(true);
        $service->syncAll();
        $elapsed = round(microtime(true) - $start, 2);

        $models = $service->getSearchableModels();
        $count = count($models);

        $this->info("Sinkronisasi {$count} model selesai dalam {$elapsed} detik.");
        $this->info('Model di-index: ' . implode(', ', array_map(fn($m) => class_basename($m), $models)));

        return self::SUCCESS;
    }
}
