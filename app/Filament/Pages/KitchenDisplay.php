<?php

namespace App\Filament\Pages;

use App\Services\KitchenDisplayService;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Notifications\Notification;

class KitchenDisplay extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-fire';

    protected static ?int $navigationSort = 620;

    protected static ?string $title = 'Dapur (KDS)';

    protected static ?string $slug = 'kitchen-display';

    protected string $view = 'filament.pages.kitchen-display';

    public static function getNavigationGroup(): ?string
    {
        return 'POS & Retail';
    }

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    protected function service(): KitchenDisplayService
    {
        return app(KitchenDisplayService::class);
    }

    public function getDashboardProperty(): array
    {
        return $this->service()->getKdsDashboard();
    }

    public function updateItemStatus(int $itemId, string $status): void
    {
        try {
            $this->service()->updateItemStatus($itemId, $status);

            Notification::make()
                ->title('Item diperbarui')
                ->body($this->statusLabel($status))
                ->success()
                ->send();
        } catch (\Throwable $e) {
            $this->notifyError($e->getMessage());
        }
    }

    public function updateOrderStatus(int $orderId, string $status): void
    {
        try {
            $this->service()->updateOrderStatus($orderId, $status);

            Notification::make()
                ->title('Order diperbarui')
                ->body($this->statusLabel($status))
                ->success()
                ->send();
        } catch (\Throwable $e) {
            $this->notifyError($e->getMessage());
        }
    }

    public function statusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Menunggu',
            'preparing' => 'Sedang Disiapkan',
            'ready' => 'Siap Disajikan',
            'served' => 'Sudah Disajikan',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($status),
        };
    }

    protected function notifyError(string $body): void
    {
        Notification::make()
            ->title('Gagal')
            ->body($body)
            ->danger()
            ->send();
    }
}
