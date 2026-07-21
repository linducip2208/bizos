<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Services\AppSwitcherService;
use App\Services\FavoritesService;
use Filament\Facades\Filament;

class AppSwitcher extends Component
{
    public string $activeApp = 'home';
    public array $favorites = [];
    public array $recentlyViewed = [];
    public array $apps = [];
    public bool $showGrid = false;
    public string $searchQuery = '';

    protected $listeners = [
        'refreshFavorites' => '$refresh',
        'toggleAppGrid' => 'toggleGrid',
        'closeAppGrid' => 'closeGrid',
    ];

    public function mount(): void
    {
        $service = app(AppSwitcherService::class);
        $this->apps = $service->getApps();
        $this->loadFavoritesAndRecent();
    }

    public function loadFavoritesAndRecent(): void
    {
        $user = auth()->user();
        if (!$user) return;

        $favService = app(FavoritesService::class);

        $this->favorites = $favService->getFavorites($user)
            ->map(fn($f) => [
                'type' => $f->resource_type,
                'label' => $f->resource_label,
                'url' => $f->resource_url,
                'icon' => $f->resource_icon,
                'is_favorite' => true,
            ])
            ->toArray();

        $this->recentlyViewed = $favService->getRecentlyViewed($user)
            ->map(fn($r) => [
                'type' => $r->resource_type,
                'label' => $r->resource_label,
                'url' => $r->resource_url,
                'icon' => $r->resource_icon,
            ])
            ->toArray();
    }

    public function toggleGrid(): void
    {
        $this->showGrid = !$this->showGrid;
        if ($this->showGrid) {
            $this->loadFavoritesAndRecent();
        }
    }

    public function closeGrid(): void
    {
        $this->showGrid = false;
    }

    public function switchApp(string $appKey): void
    {
        $this->activeApp = $appKey;
    }

    public function toggleFavorite(string $type, string $label, string $url, string $icon): void
    {
        $user = auth()->user();
        if (!$user) return;

        $favService = app(FavoritesService::class);
        $favService->toggleFavorite($user, $type, $label, $url, $icon);
        $this->loadFavoritesAndRecent();
        $this->dispatch('favorites-updated');
    }

    public function removeFavorite(string $type): void
    {
        $user = auth()->user();
        if (!$user) return;

        \App\Models\UserFavorite::where('user_id', $user->id)
            ->where('resource_type', $type)
            ->delete();

        $this->loadFavoritesAndRecent();
        $this->dispatch('favorites-updated');
    }

    public function getCardClasses(string $color, string $key): string
    {
        $base = 'group relative flex flex-col items-center gap-2 p-4 rounded-xl border transition-all duration-200';

        $activeClasses = [
            'indigo' => 'border-indigo-300 bg-indigo-50 dark:bg-indigo-900/20 ring-2 ring-indigo-500/20',
            'blue' => 'border-blue-300 bg-blue-50 dark:bg-blue-900/20 ring-2 ring-blue-500/20',
            'emerald' => 'border-emerald-300 bg-emerald-50 dark:bg-emerald-900/20 ring-2 ring-emerald-500/20',
            'amber' => 'border-amber-300 bg-amber-50 dark:bg-amber-900/20 ring-2 ring-amber-500/20',
            'violet' => 'border-violet-300 bg-violet-50 dark:bg-violet-900/20 ring-2 ring-violet-500/20',
            'rose' => 'border-rose-300 bg-rose-50 dark:bg-rose-900/20 ring-2 ring-rose-500/20',
            'cyan' => 'border-cyan-300 bg-cyan-50 dark:bg-cyan-900/20 ring-2 ring-cyan-500/20',
            'fuchsia' => 'border-fuchsia-300 bg-fuchsia-50 dark:bg-fuchsia-900/20 ring-2 ring-fuchsia-500/20',
        ];

        $inactive = 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:shadow-md';

        if ($this->activeApp === $key) {
            return $base . ' ' . ($activeClasses[$color] ?? $activeClasses['indigo']);
        }

        return $base . ' ' . $inactive;
    }

    public function getCardIconClasses(string $color): string
    {
        $classes = [
            'indigo' => 'from-indigo-500 to-indigo-700',
            'blue' => 'from-blue-500 to-blue-700',
            'emerald' => 'from-emerald-500 to-emerald-700',
            'amber' => 'from-amber-500 to-amber-700',
            'violet' => 'from-violet-500 to-violet-700',
            'rose' => 'from-rose-500 to-rose-700',
            'cyan' => 'from-cyan-500 to-cyan-700',
            'fuchsia' => 'from-fuchsia-500 to-fuchsia-700',
        ];

        return 'w-11 h-11 rounded-xl bg-gradient-to-br ' . ($classes[$color] ?? $classes['indigo']) . ' flex items-center justify-center text-white shadow-lg group-hover:scale-110 transition-transform duration-200';
    }

    public function getFilteredAppsProperty(): array
    {
        if (empty($this->searchQuery)) {
            return $this->apps;
        }

        return array_filter($this->apps, function ($app) {
            return stripos($app['name'], $this->searchQuery) !== false
                || stripos(implode(' ', $app['groups']), $this->searchQuery) !== false;
        });
    }

    public function render()
    {
        return view('livewire.app-switcher');
    }
}
