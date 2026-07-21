<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup {--keep=7 : Number of days to keep backups} {--type=auto : Backup type (auto/manual)}';

    protected $description = 'Backup database via mysqldump and store to storage/app/backups/.';

    public function handle(): int
    {
        $keepDays = (int) $this->option('keep');

        $backupDir = storage_path('app/backups');

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $db = config('database.connections.mysql');

        $filename = 'backup_' . now()->format('Y-m-d_H-i-s') . '.sql';
        $filepath = $backupDir . DIRECTORY_SEPARATOR . $filename;

        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s --port=%s %s --no-tablespaces > %s',
            escapeshellarg($db['username']),
            escapeshellarg($db['password']),
            escapeshellarg($db['host']),
            escapeshellarg((string) ($db['port'] ?? 3306)),
            escapeshellarg($db['database']),
            escapeshellarg($filepath)
        );

        $output = null;
        $exitCode = 0;
        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            $this->error('Backup failed with exit code: ' . $exitCode);
        $type = $this->option('type') ?? 'auto';
            $this->logBackup($filename, 'failed', implode("\n", $output), null, $type);

            return self::FAILURE;
        }

        $size = filesize($filepath);
        $type = $this->option('type') ?? 'auto';
        $this->info("Backup created: {$filename} (" . $this->formatBytes($size) . ")");
        $this->logBackup($filename, 'success', null, $size, $type);

        $this->cleanupOldBackups($backupDir, $keepDays);

        return self::SUCCESS;
    }

    protected function cleanupOldBackups(string $backupDir, int $keepDays): void
    {
        $cutoff = now()->subDays($keepDays)->timestamp;

        $files = glob($backupDir . DIRECTORY_SEPARATOR . 'backup_*.sql');

        $deleted = 0;
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
                $deleted++;
            }
        }

        if ($deleted > 0) {
            $this->info("Cleaned up {$deleted} old backup(s).");
        }
    }

    protected function logBackup(string $filename, string $status, ?string $error = null, ?int $size = null, string $type = 'auto'): void
    {
        DB::table('backup_logs')->insert([
            'filename' => $filename,
            'file_size' => $size,
            'type' => $type,
            'status' => $status,
            'error_message' => $error,
            'created_at' => now(),
        ]);
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        $size = $bytes;

        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }

        return round($size, 2) . ' ' . $units[$i];
    }
}
