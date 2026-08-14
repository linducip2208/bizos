<?php

namespace App\Services;

use App\Models\AiProvider;
use App\Models\Role;
use App\Models\SmsGateway;
use App\Models\User;
use App\Models\Webhook;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SystemHealthService
{
    public function getHealthReport(): array
    {
        return Cache::remember('system_health_report', 300, function () {
            return [
                'application' => $this->getApplicationInfo(),
                'database' => $this->getDatabaseInfo(),
                'cache' => $this->getCacheInfo(),
                'queue' => $this->getQueueInfo(),
                'storage' => $this->getStorageInfo(),
                'scheduler' => $this->getSchedulerInfo(),
                'integrations' => $this->getIntegrationsInfo(),
                'security' => $this->getSecurityInfo(),
            ];
        });
    }

    public function clearCache(): void
    {
        Cache::forget('system_health_report');
    }

    protected function getApplicationInfo(): array
    {
        return [
            'name' => config('app.name'),
            'version' => app()->version(),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? php_uname('s') . ' ' . php_uname('r'),
            'timezone' => config('app.timezone'),
        ];
    }

    protected function getDatabaseInfo(): array
    {
        $connection = config('database.default');
        $status = 'error';
        $sizeMb = 0;
        $tablesCount = 0;
        $migrationsPending = 0;

        try {
            DB::connection()->getPdo();
            $status = 'connected';

            $dbName = DB::connection()->getDatabaseName();
            $driver = DB::connection()->getDriverName();

            if ($driver === 'mysql' || $driver === 'mariadb') {
                $sizeResult = DB::select(
                    "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                     FROM information_schema.tables
                     WHERE table_schema = ?",
                    [$dbName]
                );
                $sizeMb = $sizeResult[0]->size_mb ?? 0;
                $tablesResult = DB::select("SELECT COUNT(*) AS cnt FROM information_schema.tables WHERE table_schema = ?", [$dbName]);
                $tablesCount = $tablesResult[0]->cnt ?? 0;
            } elseif ($driver === 'pgsql') {
                $sizeResult = DB::select("SELECT ROUND(pg_database_size(?), 2) AS size_mb", [$dbName]);
                $sizeMb = $sizeResult[0]->size_mb ?? 0;
            } elseif ($driver === 'sqlite') {
                $dbPath = config('database.connections.sqlite.database');
                if ($dbPath && File::exists($dbPath)) {
                    $sizeMb = round(File::size($dbPath) / 1024 / 1024, 2);
                }
                $tablesResult = DB::select("SELECT COUNT(*) AS cnt FROM sqlite_master WHERE type = 'table'");
                $tablesCount = $tablesResult[0]->cnt ?? 0;
            } else {
                $sizeMb = 0;
            }

            if ($driver === 'mysql' || $driver === 'mariadb' || $driver === 'pgsql') {
                $tablesResult = DB::select(
                    "SELECT COUNT(*) AS cnt FROM information_schema.tables WHERE table_schema = ?",
                    [$dbName]
                );
                $tablesCount = $tablesResult[0]->cnt ?? 0;
            }
        } catch (\Throwable $e) {
            $status = 'error: ' . $e->getMessage();
        }

        try {
            $ran = DB::table('migrations')->count();
            $migrationFiles = collect(File::files(database_path('migrations')))
                ->map(fn($f) => $f->getFilenameWithoutExtension())
                ->toArray();
            $ranMigrations = DB::table('migrations')->pluck('migration')->toArray();
            $migrationsPending = count(array_diff($migrationFiles, $ranMigrations));
        } catch (\Throwable $e) {
            $migrationsPending = -1;
        }

        return [
            'connection' => $connection,
            'driver' => DB::connection()->getDriverName(),
            'database_name' => DB::connection()->getDatabaseName(),
            'status' => $status,
            'size_mb' => (float) $sizeMb,
            'tables_count' => (int) $tablesCount,
            'migrations_pending' => $migrationsPending,
        ];
    }

    protected function getCacheInfo(): array
    {
        $driver = config('cache.default');
        $status = 'ok';

        try {
            $testKey = 'healthcheck_' . time();
            Cache::put($testKey, 'healthy', 10);
            $value = Cache::get($testKey);
            Cache::forget($testKey);

            if ($value !== 'healthy') {
                $status = 'error: cache write/read mismatch';
            }
        } catch (\Throwable $e) {
            $status = 'error: ' . $e->getMessage();
        }

        return [
            'driver' => $driver,
            'status' => $status,
            'prefix' => config('cache.prefix'),
        ];
    }

    protected function getQueueInfo(): array
    {
        $driver = config('queue.default');
        $pendingJobs = 0;
        $failedJobs = 0;

        try {
            $pendingJobs = DB::table('jobs')->count();
        } catch (\Throwable $e) {
        }

        try {
            $failedJobs = DB::table('failed_jobs')->count();
        } catch (\Throwable $e) {
        }

        if ($failedJobs > 10) {
            $status = 'error';
        } elseif ($failedJobs > 0 || $pendingJobs > 1000) {
            $status = 'warning';
        } else {
            $status = 'ok';
        }

        return [
            'driver' => $driver,
            'pending_jobs' => $pendingJobs,
            'failed_jobs' => $failedJobs,
            'status' => $status,
        ];
    }

    protected function getStorageInfo(): array
    {
        $disk = config('filesystems.default');
        $basePath = base_path();
        $totalGb = 0;
        $freeGb = 0;
        $usedPercent = 0;

        try {
            $totalBytes = @disk_total_space($basePath);
            $freeBytes = @disk_free_space($basePath);

            if ($totalBytes && $freeBytes) {
                $totalGb = round($totalBytes / 1024 / 1024 / 1024, 2);
                $freeGb = round($freeBytes / 1024 / 1024 / 1024, 2);
                $usedPercent = $totalGb > 0 ? round((($totalGb - $freeGb) / $totalGb) * 100, 1) : 0;
            }
        } catch (\Throwable $e) {
        }

        return [
            'disk' => $disk,
            'path' => $basePath,
            'total_gb' => $totalGb,
            'free_gb' => $freeGb,
            'used_gb' => round($totalGb - $freeGb, 2),
            'used_percent' => $usedPercent,
        ];
    }

    protected function getSchedulerInfo(): array
    {
        $lastRun = Cache::get('scheduler_last_run');
        $status = 'unknown';

        if ($lastRun) {
            $lastRunTime = is_numeric($lastRun) ? \Carbon\Carbon::createFromTimestamp($lastRun) : \Carbon\Carbon::parse($lastRun);
            $minutesAgo = $lastRunTime->diffInMinutes(now());

            $status = $minutesAgo < 10 ? 'ok' : 'warning';
        }

        $healthChecks = \App\Models\SystemHealthCheck::where('check_type', 'scheduler')
            ->latest('checked_at')
            ->first();

        if ($healthChecks && !$lastRun) {
            $lastRun = $healthChecks->checked_at->format('Y-m-d H:i:s');
            $status = $healthChecks->checked_at->diffInMinutes(now()) < 10 ? 'ok' : 'warning';
        }

        return [
            'last_run' => $lastRun ?: 'Belum pernah',
            'status' => $status,
            'running_since' => Cache::get('scheduler_running_since'),
        ];
    }

    protected function getIntegrationsInfo(): array
    {
        $companyId = auth()->check() ? auth()->user()->company_id : null;

        return [
            'sms_gateway' => [
                'available' => class_exists(SmsGateway::class),
                'active_count' => $companyId
                    ? SmsGateway::where('company_id', $companyId)->where('is_active', true)->count()
                    : SmsGateway::where('is_active', true)->count(),
                'total_count' => $companyId
                    ? SmsGateway::where('company_id', $companyId)->count()
                    : SmsGateway::count(),
            ],
            'ai_providers' => [
                'available' => class_exists(AiProvider::class),
                'active_count' => $companyId
                    ? AiProvider::where('company_id', $companyId)->where('is_active', true)->count()
                    : AiProvider::where('is_active', true)->count(),
                'total_count' => $companyId
                    ? AiProvider::where('company_id', $companyId)->count()
                    : AiProvider::count(),
            ],
            'webhooks' => [
                'available' => class_exists(Webhook::class),
                'active_count' => $companyId
                    ? Webhook::where('company_id', $companyId)->where('is_active', true)->count()
                    : Webhook::where('is_active', true)->count(),
                'total_count' => $companyId
                    ? Webhook::where('company_id', $companyId)->count()
                    : Webhook::count(),
            ],
        ];
    }

    protected function getSecurityInfo(): array
    {
        return [
            'users_count' => User::count(),
            'roles_count' => Role::count(),
            'admin_route' => config('app.admin_route_prefix', 'admin'),
            'app_url' => config('app.url'),
            'https' => request()->secure() || str_starts_with(config('app.url'), 'https'),
        ];
    }
}
