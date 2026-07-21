<?php

namespace App\Services;

use App\Models\FeatureFlag;
use App\Models\JobMonitor;
use App\Models\Plugin;
use App\Models\QueueMonitor;
use App\Models\SystemHealthCheck;
use App\Models\SystemLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class PlatformService
{
    // ──────────────────────────────────────────────
    //  PLUGIN MANAGEMENT
    // ──────────────────────────────────────────────

    public function installPlugin(array $data): Plugin
    {
        $plugin = Plugin::updateOrCreate(
            ['company_id' => $data['company_id'], 'name' => $data['name']],
            [
                'version' => $data['version'] ?? '1.0.0',
                'author' => $data['author'] ?? null,
                'description' => $data['description'] ?? null,
                'status' => 'installed',
                'config' => $data['config'] ?? [],
            ]
        );

        $this->log($data['company_id'], 'info', 'plugin', "Plugin {$plugin->name} v{$plugin->version} berhasil diinstal.");

        return $plugin;
    }

    public function activatePlugin(Plugin $plugin): Plugin
    {
        $plugin->update(['status' => 'active']);
        $this->log($plugin->company_id, 'info', 'plugin', "Plugin {$plugin->name} diaktifkan.");
        return $plugin->fresh();
    }

    public function deactivatePlugin(Plugin $plugin): Plugin
    {
        $plugin->update(['status' => 'inactive']);
        $this->log($plugin->company_id, 'info', 'plugin', "Plugin {$plugin->name} dinonaktifkan.");
        return $plugin->fresh();
    }

    public function uninstallPlugin(Plugin $plugin): void
    {
        $name = $plugin->name;
        $plugin->delete();
        $this->log($plugin->company_id, 'info', 'plugin', "Plugin {$name} dihapus.");
    }

    public function getInstalledPlugins(int $companyId): array
    {
        return Plugin::where('company_id', $companyId)
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    // ──────────────────────────────────────────────
    //  FEATURE FLAGS
    // ──────────────────────────────────────────────

    public function enableFeature(string $name, int $companyId): void
    {
        $flag = FeatureFlag::updateOrCreate(
            ['company_id' => $companyId, 'name' => $name],
            [
                'description' => "Fitur {$name}",
                'is_enabled' => true,
                'enabled_at' => now(),
            ]
        );

        Cache::forget("feature_flag:{$companyId}:{$name}");
        $this->log($companyId, 'info', 'feature_flag', "Fitur {$name} diaktifkan.");
    }

    public function disableFeature(string $name, int $companyId): void
    {
        FeatureFlag::where('company_id', $companyId)
            ->where('name', $name)
            ->update(['is_enabled' => false]);

        Cache::forget("feature_flag:{$companyId}:{$name}");
        $this->log($companyId, 'info', 'feature_flag', "Fitur {$name} dinonaktifkan.");
    }

    public function isFeatureEnabled(string $name, int $companyId): bool
    {
        return Cache::remember("feature_flag:{$companyId}:{$name}", 300, function () use ($name, $companyId) {
            return FeatureFlag::isEnabled($name, $companyId);
        });
    }

    public function getAllFeatureFlags(int $companyId): array
    {
        return FeatureFlag::where('company_id', $companyId)
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    // ──────────────────────────────────────────────
    //  SYSTEM HEALTH CHECK
    // ──────────────────────────────────────────────

    public function checkSystemHealth(): array
    {
        $results = [];
        $companyId = 1; // system-level check

        $results['database'] = $this->checkDatabase($companyId);
        $results['storage'] = $this->checkStorage($companyId);
        $results['cache'] = $this->checkCache($companyId);
        $results['queue'] = $this->checkQueue($companyId);
        $results['memory'] = $this->checkMemory($companyId);
        $results['cpu'] = $this->checkCpu($companyId);

        $overallStatus = 'ok';
        foreach ($results as $check) {
            if ($check['status'] === 'error') {
                $overallStatus = 'error';
                break;
            }
            if ($check['status'] === 'warning') {
                $overallStatus = 'warning';
            }
        }

        return [
            'status' => $overallStatus,
            'checked_at' => now()->toIso8601String(),
            'checks' => $results,
        ];
    }

    protected function checkDatabase(int $companyId): array
    {
        try {
            $start = microtime(true);
            DB::select('SELECT 1');
            $elapsed = round((microtime(true) - $start) * 1000, 2);

            $status = $elapsed > 500 ? 'warning' : 'ok';
            $details = ['response_ms' => $elapsed, 'connection' => config('database.default')];

            SystemHealthCheck::create([
                'company_id' => $companyId,
                'check_type' => 'database',
                'status' => $status,
                'details' => $details,
                'checked_at' => now(),
            ]);

            return ['status' => $status, 'message' => "Koneksi database OK ({$elapsed}ms)", 'details' => $details];
        } catch (\Exception $e) {
            SystemHealthCheck::create([
                'company_id' => $companyId,
                'check_type' => 'database',
                'status' => 'error',
                'details' => ['error' => $e->getMessage()],
                'checked_at' => now(),
            ]);
            return ['status' => 'error', 'message' => 'Koneksi database GAGAL: ' . $e->getMessage()];
        }
    }

    protected function checkStorage(int $companyId): array
    {
        try {
            $storagePath = storage_path('app');
            $freeSpace = disk_free_space($storagePath);
            $totalSpace = disk_total_space($storagePath);
            $usedPercent = $totalSpace > 0 ? round((1 - $freeSpace / $totalSpace) * 100, 1) : 0;

            $status = $usedPercent > 90 ? 'error' : ($usedPercent > 75 ? 'warning' : 'ok');
            $details = [
                'total_gb' => round($totalSpace / 1024 / 1024 / 1024, 2),
                'free_gb' => round($freeSpace / 1024 / 1024 / 1024, 2),
                'used_percent' => $usedPercent,
            ];

            SystemHealthCheck::create([
                'company_id' => $companyId,
                'check_type' => 'storage',
                'status' => $status,
                'details' => $details,
                'checked_at' => now(),
            ]);

            return ['status' => $status, 'message' => "Storage: {$details['free_gb']}GB tersedia ({$usedPercent}% terpakai)", 'details' => $details];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Pengecekan storage GAGAL: ' . $e->getMessage()];
        }
    }

    protected function checkCache(int $companyId): array
    {
        try {
            $testKey = 'health_check_' . time();
            Cache::put($testKey, 'ok', 10);
            $value = Cache::get($testKey);
            Cache::forget($testKey);

            $status = $value === 'ok' ? 'ok' : 'warning';
            $details = ['driver' => config('cache.default')];

            SystemHealthCheck::create([
                'company_id' => $companyId,
                'check_type' => 'cache',
                'status' => $status,
                'details' => $details,
                'checked_at' => now(),
            ]);

            return ['status' => $status, 'message' => "Cache OK (driver: {$details['driver']})", 'details' => $details];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Pengecekan cache GAGAL: ' . $e->getMessage()];
        }
    }

    protected function checkQueue(int $companyId): array
    {
        try {
            $queueConnection = config('queue.default');

            // Count jobs in queues
            $pendingCount = DB::table('jobs')->whereNull('reserved_at')->count();
            $reservedCount = DB::table('jobs')->whereNotNull('reserved_at')->count();
            $failedCount = DB::table('failed_jobs')->count();

            $status = $failedCount > 100 ? 'error' : ($failedCount > 10 ? 'warning' : 'ok');
            $details = [
                'connection' => $queueConnection,
                'pending' => $pendingCount,
                'processing' => $reservedCount,
                'failed' => $failedCount,
            ];

            SystemHealthCheck::create([
                'company_id' => $companyId,
                'check_type' => 'queue',
                'status' => $status,
                'details' => $details,
                'checked_at' => now(),
            ]);

            $message = "Queue: {$pendingCount} pending, {$reservedCount} diproses, {$failedCount} gagal";
            return ['status' => $status, 'message' => $message, 'details' => $details];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Pengecekan queue GAGAL: ' . $e->getMessage()];
        }
    }

    protected function checkMemory(int $companyId): array
    {
        $memoryLimit = ini_get('memory_limit');
        $currentUsage = memory_get_usage(true);
        $limitBytes = $this->parseMemoryLimit($memoryLimit);

        $usedPercent = $limitBytes > 0 ? round(($currentUsage / $limitBytes) * 100, 2) : 0;
        $status = $usedPercent > 90 ? 'error' : ($usedPercent > 70 ? 'warning' : 'ok');

        $details = [
            'memory_limit' => $memoryLimit,
            'current_usage_mb' => round($currentUsage / 1048576, 2),
            'usage_percent' => $usedPercent,
        ];

        SystemHealthCheck::create([
            'company_id' => $companyId,
            'check_type' => 'memory',
            'status' => $status,
            'details' => $details,
            'checked_at' => now(),
        ]);

        return ['status' => $status, 'message' => "Memory: {$details['current_usage_mb']}MB / {$memoryLimit}", 'details' => $details];
    }

    protected function checkCpu(int $companyId): array
    {
        $load = null;
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
        } elseif (PHP_OS_FAMILY === 'Windows') {
            $load = [0.0, 0.0, 0.0];
        }

        $status = 'ok';
        $details = ['load_avg' => $load, 'cpu_cores' => 1];

        if ($load) {
            $load1m = $load[0];
            $status = $load1m > 2.0 ? 'warning' : 'ok';
            $details['load_1m'] = $load1m;
            $details['load_5m'] = $load[1] ?? null;
            $details['load_15m'] = $load[2] ?? null;
        }

        SystemHealthCheck::create([
            'company_id' => $companyId,
            'check_type' => 'cpu',
            'status' => $status,
            'details' => $details,
            'checked_at' => now(),
        ]);

        return ['status' => $status, 'message' => 'CPU load: ' . ($load ? round($load[0], 2) : 'N/A'), 'details' => $details];
    }

    // ──────────────────────────────────────────────
    //  QUEUE MONITOR
    // ──────────────────────────────────────────────

    public function monitorQueue(string $queueName): QueueMonitor
    {
        $companyId = 1; // System-level

        $pending = DB::table('jobs')->where('queue', $queueName)->whereNull('reserved_at')->count();
        $processing = DB::table('jobs')->where('queue', $queueName)->whereNotNull('reserved_at')->count();
        $failed = DB::table('failed_jobs')->where('queue', $queueName)->count();

        return QueueMonitor::updateOrCreate(
            ['company_id' => $companyId, 'queue_name' => $queueName],
            [
                'pending_count' => $pending,
                'processing_count' => $processing,
                'failed_count' => $failed,
                'checked_at' => now(),
            ]
        );
    }

    public function monitorAllQueues(): array
    {
        $queues = DB::table('jobs')->distinct()->pluck('queue')->toArray();
        $results = [];

        foreach ($queues as $queue) {
            if (empty($queue)) $queue = 'default';
            $results[$queue] = $this->monitorQueue($queue);
        }

        if (empty($results)) {
            $results['default'] = $this->monitorQueue('default');
        }

        return $results;
    }

    // ──────────────────────────────────────────────
    //  JOB MONITOR
    // ──────────────────────────────────────────────

    public function trackJob(int $companyId, string $jobId, string $jobName): JobMonitor
    {
        return JobMonitor::create([
            'company_id' => $companyId,
            'job_id' => $jobId,
            'job_name' => $jobName,
            'status' => 'pending',
            'progress_percent' => 0,
            'started_at' => now(),
        ]);
    }

    public function updateJobProgress(JobMonitor $monitor, int $percent): void
    {
        $monitor->update([
            'progress_percent' => min(100, max(0, $percent)),
            'status' => $percent >= 100 ? 'completed' : 'running',
        ]);
    }

    public function completeJob(JobMonitor $monitor): void
    {
        $monitor->update([
            'status' => 'completed',
            'progress_percent' => 100,
            'completed_at' => now(),
        ]);
        $this->log($monitor->company_id, 'info', 'job', "Job {$monitor->job_name} selesai.");
    }

    public function failJob(JobMonitor $monitor, string $errorMessage): void
    {
        $monitor->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'completed_at' => now(),
        ]);
        $this->log($monitor->company_id, 'error', 'job', "Job {$monitor->job_name} gagal: {$errorMessage}");
    }

    public function getRunningJobs(int $companyId): array
    {
        return JobMonitor::where('company_id', $companyId)
            ->running()
            ->orderBy('started_at', 'desc')
            ->get()
            ->toArray();
    }

    public function getFailedJobs(int $companyId, int $limit = 20): array
    {
        return JobMonitor::where('company_id', $companyId)
            ->failed()
            ->orderBy('completed_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    // ──────────────────────────────────────────────
    //  SYSTEM LOGS
    // ──────────────────────────────────────────────

    public function log(int $companyId, string $level, string $channel, string $message, ?array $context = null): SystemLog
    {
        return SystemLog::create([
            'company_id' => $companyId,
            'level' => $level,
            'channel' => $channel,
            'message' => $message,
            'context' => $context,
        ]);
    }

    public function getLogs(int $companyId, ?string $level = null, int $limit = 100): array
    {
        $query = SystemLog::where('company_id', $companyId)->latest('created_at');

        if ($level) {
            $query->byLevel($level);
        }

        return $query->limit($limit)->get()->toArray();
    }

    public function purgeOldLogs(int $days = 30): int
    {
        $count = SystemLog::where('created_at', '<', now()->subDays($days))->count();
        SystemLog::where('created_at', '<', now()->subDays($days))->delete();
        return $count;
    }

    // ──────────────────────────────────────────────
    //  HEALTH SUMMARY
    // ──────────────────────────────────────────────

    public function getHealthSummary(): array
    {
        $health = $this->checkSystemHealth();

        // Get latest health checks
        $latestChecks = [];
        $checkTypes = ['database', 'storage', 'cache', 'queue', 'memory', 'cpu'];

        foreach ($checkTypes as $type) {
            $latestChecks[$type] = SystemHealthCheck::where('check_type', $type)
                ->latest('checked_at')
                ->first();
        }

        return [
            'overall_status' => $health['status'],
            'checked_at' => $health['checked_at'],
            'checks' => $health['checks'],
            'latest_issues' => SystemLog::where('level', 'error')
                ->where('created_at', '>=', now()->subHours(24))
                ->latest('created_at')
                ->limit(10)
                ->get()
                ->toArray(),
        ];
    }

    protected function parseMemoryLimit(string $limit): int
    {
        $unit = strtoupper(substr($limit, -1));
        $value = (int) substr($limit, 0, -1);

        return match ($unit) {
            'G' => $value * 1024 * 1024 * 1024,
            'M' => $value * 1024 * 1024,
            'K' => $value * 1024,
            default => (int) $limit,
        };
    }
}
