<?php

namespace App\Filament\Pages;

use App\Services\SystemHealthService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SystemHealth extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-heart';

    protected static ?int $navigationSort = 444;

    protected static ?string $title = 'Kesehatan Sistem';

    protected static ?string $slug = 'system-health';

    protected string $view = 'filament.pages.system-health';

    public array $report = [];

    public static function getNavigationGroup(): ?string
    {
        return '⚙️ System';
    }

    public function mount(SystemHealthService $service): void
    {
        $this->report = $service->getHealthReport();
    }

    public function pollReport(SystemHealthService $service): void
    {
        $this->report = $service->getHealthReport();
    }

    public function refreshReport(SystemHealthService $service): void
    {
        $service->clearCache();
        $this->report = $service->getHealthReport();

        Notification::make()
            ->title('Laporan diperbarui')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->action(fn() => $this->refreshReport(app(SystemHealthService::class))),
        ];
    }

}
