<?php

namespace App\Filament\Pages;

use BackedEnum;
use App\Models\MarketplaceApp;
use App\Models\MarketplaceInstall;
use App\Services\MarketplaceService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class AppStore extends Page
{
    protected static ?string $title = 'App Store';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'app-store';

    protected string $view = 'filament.pages.app-store';

    public string $activeTab = 'browse';
    public string $search = '';
    public ?string $selectedCategory = null;
    public ?int $selectedAppId = null;
    public Collection $apps;
    public Collection $installedApps;
    public Collection $featuredApps;
    public ?MarketplaceApp $selectedApp = null;
    public array $categories = [];
    public array $installedIds = [];

    public static function getNavigationGroup(): ?string
    {
        return 'Platform';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function mount(): void
    {
        $service = app(MarketplaceService::class);
        $companyId = auth()->user()->company_id;

        $this->apps = $service->getStoreListing();
        $this->featuredApps = $service->getFeaturedApps();
        $this->installedApps = $service->getInstalledApps($companyId);
        $this->installedIds = $this->installedApps->pluck('marketplace_app_id')->toArray();
        $this->categories = $service->getCategories();
    }

    public function browseApp(int $appId): void
    {
        $this->selectedApp = MarketplaceApp::find($appId);
        $this->selectedAppId = $appId;
    }

    public function closeAppDetail(): void
    {
        $this->selectedApp = null;
        $this->selectedAppId = null;
    }

    public function installApp(int $appId): void
    {
        try {
            $service = app(MarketplaceService::class);
            $service->installApp($appId, auth()->user()->company_id);

            Notification::make()
                ->title('Aplikasi berhasil di-install')
                ->success()
                ->send();

            $this->installedApps = $service->getInstalledApps(auth()->user()->company_id);
            $this->installedIds = $this->installedApps->pluck('marketplace_app_id')->toArray();

        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal install: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function uninstallApp(int $appId): void
    {
        try {
            $service = app(MarketplaceService::class);
            $service->uninstallApp($appId, auth()->user()->company_id);

            Notification::make()
                ->title('Aplikasi berhasil di-uninstall')
                ->success()
                ->send();

            $this->installedApps = $service->getInstalledApps(auth()->user()->company_id);
            $this->installedIds = $this->installedApps->pluck('marketplace_app_id')->toArray();

        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal uninstall: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function updateApp(int $appId): void
    {
        try {
            $service = app(MarketplaceService::class);
            $service->updateApp($appId, auth()->user()->company_id);

            Notification::make()
                ->title('Aplikasi berhasil di-update')
                ->success()
                ->send();

            $this->installedApps = $service->getInstalledApps(auth()->user()->company_id);
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal update: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function updatedSearch(): void
    {
        $this->filterApps();
    }

    public function updatedSelectedCategory(): void
    {
        $this->filterApps();
    }

    public function filterApps(): void
    {
        $service = app(MarketplaceService::class);
        $this->apps = $service->getStoreListing(
            $this->selectedCategory,
            $this->search
        );
    }

    public function isInstalled(int $appId): bool
    {
        return in_array($appId, $this->installedIds);
    }
}
