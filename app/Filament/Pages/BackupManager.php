<?php

namespace App\Filament\Pages;

use App\Services\BackupService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class BackupManager extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-server-stack';

    protected static ?int $navigationSort = 1010;

    protected static ?string $title = 'Backup Database';

    protected static ?string $navigationLabel = 'Backup DB';

    protected string $view = 'filament.pages.backup-manager';

    public array $backups = [];

    public static function getNavigationGroup(): ?string
    {
        return 'Sistem';
    }

    public function mount(BackupService $backupService): void
    {
        $this->backups = $backupService->listBackups();
    }

    public function createBackup(BackupService $backupService): void
    {
        try {
            $filename = $backupService->createBackup('manual');
            Notification::make()->title('Backup berhasil dibuat')->body($filename)->success()->send();
            $this->backups = $backupService->listBackups();
        } catch (\Throwable $e) {
            Notification::make()->title('Gagal membuat backup')->body($e->getMessage())->danger()->send();
        }
    }

    public function deleteBackup(string $filename, BackupService $backupService): void
    {
        try {
            $backupService->deleteBackup($filename);
            Notification::make()->title('Backup dihapus')->success()->send();
            $this->backups = $backupService->listBackups();
        } catch (\Throwable $e) {
            Notification::make()->title('Gagal menghapus')->body($e->getMessage())->danger()->send();
        }
    }

    public function downloadBackup(string $filename, BackupService $backupService)
    {
        try {
            return $backupService->downloadBackup($filename);
        } catch (\Throwable $e) {
            Notification::make()->title('Gagal download')->body($e->getMessage())->danger()->send();
            return null;
        }
    }

    public function uploadToCloud(string $filename, BackupService $backupService): void
    {
        try {
            $url = $backupService->uploadToCloud($filename);
            Notification::make()->title('Berhasil upload ke cloud')->body($url)->success()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Gagal upload')->body($e->getMessage())->danger()->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_backup')
                ->label('Buat Backup Sekarang')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->action(fn() => $this->createBackup(app(BackupService::class))),
        ];
    }
}
