<?php

namespace App\Filament\Pages;

use App\Models\IntegrationConnector;
use App\Models\IntegrationSyncLog;
use App\Services\IntegrationHubService;
use Filament\Pages\Page;

class IntegrationHubDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-link';

    protected static ?int $navigationSort = 6;

    protected static string $view = 'filament.pages.integration-hub-dashboard';

    protected static ?string $title = 'Hub Integrasi';

    public array $connectors = [];
    public array $syncLogs = [];

    public static function getNavigationGroup(): ?string
    {
        return 'Hub Integrasi';
    }

    public function mount(): void
    {
        $user = auth()->user();
        if (!$user || !$user->company_id) {
            return;
        }

        $service = app(IntegrationHubService::class);
        $this->connectors = $service->getConnectorCatalog($user->company_id);
        $this->syncLogs = $service->getSyncLogs($user->company_id)->toArray();
    }
}
