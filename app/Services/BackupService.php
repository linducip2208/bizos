<?php

namespace App\Services;

use App\Models\BackupLog;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackupService
{
    protected function backupDir(): string
    {
        $dir = storage_path('app/backups');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    public function createBackup(string $type = 'manual'): string
    {
        $db = config('database.connections.mysql');
        $backupDir = $this->backupDir();

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
        exec($command . ' 2>&1', $output, $exitCode);

        $size = file_exists($filepath) ? filesize($filepath) : null;

        BackupLog::create([
            'filename' => $filename,
            'file_size' => $size,
            'type' => $type,
            'status' => $exitCode === 0 ? 'success' : 'failed',
            'error_message' => $exitCode !== 0 ? implode("\n", $output) : null,
            'created_at' => now(),
        ]);

        if ($exitCode !== 0) {
            throw new \RuntimeException('Backup failed: ' . implode("\n", $output));
        }

        return $filename;
    }

    public function listBackups(): array
    {
        $backupDir = $this->backupDir();
        $files = glob($backupDir . DIRECTORY_SEPARATOR . 'backup_*.sql');
        rsort($files);

        $result = [];
        foreach ($files as $file) {
            $filename = basename($file);
            $log = BackupLog::where('filename', $filename)->first();

            $result[] = [
                'filename' => $filename,
                'size' => filesize($file),
                'size_formatted' => $this->formatBytes(filesize($file)),
                'date' => filemtime($file),
                'type' => $log?->type ?? 'unknown',
                'status' => $log?->status ?? 'unknown',
            ];
        }

        return $result;
    }

    public function downloadBackup(string $filename): BinaryFileResponse
    {
        $filepath = $this->backupDir() . DIRECTORY_SEPARATOR . $filename;
        if (!file_exists($filepath)) {
            throw new \RuntimeException('File tidak ditemukan: ' . $filename);
        }
        return response()->download($filepath);
    }

    public function restoreBackup(string $filename): void
    {
        $filepath = $this->backupDir() . DIRECTORY_SEPARATOR . $filename;
        if (!file_exists($filepath)) {
            throw new \RuntimeException('File tidak ditemukan: ' . $filename);
        }

        $db = config('database.connections.mysql');

        $command = sprintf(
            'mysql --user=%s --password=%s --host=%s --port=%s %s < %s',
            escapeshellarg($db['username']),
            escapeshellarg($db['password']),
            escapeshellarg($db['host']),
            escapeshellarg((string) ($db['port'] ?? 3306)),
            escapeshellarg($db['database']),
            escapeshellarg($filepath)
        );

        $output = null;
        $exitCode = 0;
        exec($command . ' 2>&1', $output, $exitCode);

        if ($exitCode !== 0) {
            throw new \RuntimeException('Restore failed: ' . implode("\n", $output));
        }
    }

    public function deleteBackup(string $filename): void
    {
        $filepath = $this->backupDir() . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        BackupLog::where('filename', $filename)->delete();
    }

    public function uploadToCloud(string $filename): string
    {
        $filepath = $this->backupDir() . DIRECTORY_SEPARATOR . $filename;
        if (!file_exists($filepath)) {
            throw new \RuntimeException('File tidak ditemukan: ' . $filename);
        }

        $disk = config('filesystems.backup_disk', 's3');
        $cloudPath = 'backups/' . $filename;

        Storage::disk($disk)->put(
            $cloudPath,
            fopen($filepath, 'r')
        );

        BackupLog::where('filename', $filename)->update(['storage_path' => $cloudPath]);

        return Storage::disk($disk)->url($cloudPath);
    }

    public function restoreFromCloud(string $filename, ?string $storagePath = null): void
    {
        $disk = config('filesystems.backup_disk', 's3');
        $cloudPath = $storagePath ?? 'backups/' . $filename;

        if (!Storage::disk($disk)->exists($cloudPath)) {
            throw new \RuntimeException('File tidak ditemukan di cloud storage: ' . $cloudPath);
        }

        $localPath = $this->backupDir() . DIRECTORY_SEPARATOR . $filename;

        $stream = Storage::disk($disk)->readStream($cloudPath);
        file_put_contents($localPath, stream_get_contents($stream));
        if (is_resource($stream)) {
            fclose($stream);
        }

        $this->restoreBackup($filename);
    }

    public function listSchedules(): array
    {
        $schedules = [];
        $settings = \App\Models\SystemSetting::class;

        $backupLogs = BackupLog::whereNotNull('schedule_name')
            ->orderByDesc('created_at')
            ->take(20)
            ->get();

        foreach ($backupLogs as $log) {
            $schedules[] = [
                'name' => $log->schedule_name,
                'last_run' => $log->created_at?->format('Y-m-d H:i:s'),
                'status' => $log->status,
                'filename' => $log->filename,
                'type' => $log->type,
            ];
        }

        if (empty($schedules)) {
            $schedules[] = [
                'name' => 'backup_harian',
                'last_run' => BackupLog::where('type', 'auto')->latest('created_at')->value('created_at'),
                'status' => 'active',
                'filename' => null,
                'type' => 'auto',
            ];
            $schedules[] = [
                'name' => 'backup_mingguan',
                'last_run' => null,
                'status' => 'active',
                'filename' => null,
                'type' => 'auto',
            ];
        }

        return $schedules;
    }

    public function scheduleAutoBackup(string $frequency = 'daily'): void
    {
        $settings = app(\App\Models\SystemSetting::class);
        $settings->where('key', 'backup_frequency')->update(['value' => $frequency]);
        $settings->where('key', 'backup_auto_enabled')->update(['value' => 'true']);
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
