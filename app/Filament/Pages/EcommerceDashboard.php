<?php

namespace App\Filament\Pages;

use App\Models\EcommerceChannel;
use App\Models\EcommerceOrder;
use App\Services\EcommerceService;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class EcommerceDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?int $navigationSort = 950;

    protected static string $view = 'filament.pages.ecommerce-dashboard';

    protected static ?string $title = 'Dashboard E-Commerce';

    public static function getNavigationGroup(): ?string
    {
        return 'E-Commerce';
    }

    public array $performance = [];
    public array $syncStatusSummary = [];
    public array $channels = [];
    public array $unmatchedSkus = [];
    public string $period = 'this_month';

    public function mount(): void
    {
        $this->period = request('period', 'this_month');
        $this->loadData();
    }

    protected function loadData(): void
    {
        $service = app(EcommerceService::class);
        $this->performance = $service->getChannelPerformance($this->period);

        $this->syncStatusSummary = $this->performance['sync_status'] ?? [];

        $this->channels = EcommerceChannel::withCount(['orders', 'inventoryLogs'])->get()->toArray();

        $this->unmatchedSkus = $service->suggestMatches();
    }

    public function getChannelStatusSummary(): array
    {
        $allOrders = EcommerceOrder::with('channel')->get();

        return $allOrders->groupBy('channel_id')
            ->map(fn($group, $channelId) => [
                'channel' => $group->first()?->channel?->channel_name ?? 'Unknown',
                'total' => $group->count(),
                'unpaid' => $group->where('channel_status', 'unpaid')->count(),
                'paid' => $group->where('channel_status', 'paid')->count(),
                'completed' => $group->whereIn('channel_status', ['completed', 'delivered'])->count(),
                'pending_sync' => $group->where('sync_status', 'pending')->count(),
            ])
            ->values()
            ->toArray();
    }
}
