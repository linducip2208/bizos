<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Filament\Facades\Filament;

class CommandPalette extends Component
{
    public string $query = '';
    public array $results = [];
    public bool $isOpen = false;
    public int $selectedIndex = 0;
    public string $mode = 'all';

    protected $listeners = [
        'openCommandPalette' => 'open',
        'closeCommandPalette' => 'close',
    ];

    public function mount(): void
    {
        $this->isOpen = false;
    }

    public function open(): void
    {
        $this->isOpen = true;
        $this->query = '';
        $this->selectedIndex = 0;
        $this->search();
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->query = '';
        $this->results = [];
        $this->selectedIndex = 0;
    }

    public function updatedQuery(): void
    {
        $this->selectedIndex = 0;
        $this->search();
    }

    public function search(): void
    {
        if (empty(trim($this->query))) {
            $this->results = $this->getQuickActions();
            return;
        }

        $navResults = $this->searchNavigation();
        $actionResults = $this->searchActions();
        $recentResults = $this->searchRecent();

        $this->results = [
            ['type' => 'header', 'label' => 'Navigasi'],
            ...$navResults,
            ['type' => 'header', 'label' => 'Aksi Cepat'],
            ...$actionResults,
            ['type' => 'header', 'label' => 'Terbaru Dilihat'],
            ...$recentResults,
        ];
    }

    protected function searchNavigation(): array
    {
        $query = mb_strtolower(trim($this->query));
        $results = [];
        $seen = [];

        foreach (Filament::getResources() as $resource) {
            if (!method_exists($resource, 'canViewAny') || !$resource::canViewAny()) {
                continue;
            }

            $label = method_exists($resource, 'getPluralModelLabel')
                ? $resource::getPluralModelLabel()
                : (method_exists($resource, 'getModelLabel') ? $resource::getModelLabel() : class_basename($resource));

            $group = method_exists($resource, 'getNavigationGroup') ? $resource::getNavigationGroup() : null;
            $url = $resource::getUrl('index');
            $icon = method_exists($resource, 'getNavigationIcon') ? $resource::getNavigationIcon() : 'heroicon-o-rectangle-stack';

            $searchText = mb_strtolower($label . ' ' . ($group ?? ''));
            if (str_contains($searchText, $query)) {
                $key = $label;
                if (isset($seen[$key])) continue;
                $seen[$key] = true;

                $iconName = $this->resolveIconName($icon);

                $results[] = [
                    'type' => 'navigation',
                    'label' => $label,
                    'group' => $group,
                    'url' => $url,
                    'icon' => $iconName,
                ];
            }
        }

        foreach (Filament::getPages() as $page) {
            if (!method_exists($page, 'canView') || !$page::canView()) {
                continue;
            }

            $label = method_exists($page, 'getTitle') ? $page::getTitle() : class_basename($page);
            $group = method_exists($page, 'getNavigationGroup') ? $page::getNavigationGroup() : null;
            $url = $page::getUrl();
            $icon = method_exists($page, 'getNavigationIcon') ? $page::getNavigationIcon() : 'heroicon-o-document';

            $searchText = mb_strtolower($label . ' ' . ($group ?? ''));
            if (str_contains($searchText, $query)) {
                $key = $label;
                if (isset($seen[$key])) continue;
                $seen[$key] = true;

                $iconName = $this->resolveIconName($icon);

                $results[] = [
                    'type' => 'navigation',
                    'label' => $label,
                    'group' => $group,
                    'url' => $url,
                    'icon' => $iconName,
                ];
            }
        }

        return array_slice($results, 0, 8);
    }

    protected function searchActions(): array
    {
        $query = mb_strtolower(trim($this->query));
        $quickActions = $this->getQuickActions();
        return array_values(array_filter($quickActions, function ($action) use ($query) {
            return str_contains(mb_strtolower($action['label']), $query);
        }));
    }

    protected function getQuickActions(): array
    {
        $actions = [];

        $favorites = \App\Models\UserFavorite::where('user_id', auth()->id())
            ->orderBy('sort_order')
            ->get();

        foreach ($favorites as $fav) {
            $actions[] = [
                'type' => 'favorite',
                'label' => $fav->resource_label,
                'url' => $fav->resource_url,
                'icon' => $fav->resource_icon ?? 'heroicon-o-star',
                'group' => 'Favorit',
            ];
        }

        $actions[] = [
            'type' => 'action',
            'label' => 'Buat Faktur Baru',
            'url' => url('/admin/invoices/create'),
            'icon' => 'heroicon-o-document-plus',
            'group' => 'Aksi Cepat',
        ];

        $actions[] = [
            'type' => 'action',
            'label' => 'Tambah Karyawan Baru',
            'url' => url('/admin/employees/create'),
            'icon' => 'heroicon-o-user-plus',
            'group' => 'Aksi Cepat',
        ];

        $actions[] = [
            'type' => 'action',
            'label' => 'Buat Tiket Baru',
            'url' => url('/admin/tickets/create'),
            'icon' => 'heroicon-o-ticket',
            'group' => 'Aksi Cepat',
        ];

        $actions[] = [
            'type' => 'action',
            'label' => 'Dashboard',
            'url' => url('/admin'),
            'icon' => 'heroicon-o-home',
            'group' => 'Aksi Cepat',
        ];

        $actions[] = [
            'type' => 'action',
            'label' => 'Laporan Bisnis',
            'url' => url('/admin/laporan-bisnis'),
            'icon' => 'heroicon-o-chart-bar',
            'group' => 'Laporan',
        ];

        $actions[] = [
            'type' => 'action',
            'label' => 'Laporan Keuangan',
            'url' => url('/admin/laporan-keuangan'),
            'icon' => 'heroicon-o-banknotes',
            'group' => 'Laporan',
        ];

        return $actions;
    }

    protected function searchRecent(): array
    {
        $query = mb_strtolower(trim($this->query));
        if (empty($query)) return [];

        $recent = \App\Models\RecentlyViewed::where('user_id', auth()->id())
            ->orderBy('viewed_at', 'desc')
            ->limit(10)
            ->get();

        $results = [];
        foreach ($recent as $r) {
            if (str_contains(mb_strtolower($r->resource_label), $query)) {
                $results[] = [
                    'type' => 'recent',
                    'label' => $r->resource_label,
                    'url' => $r->resource_url,
                    'icon' => $r->resource_icon ?? 'heroicon-o-clock',
                ];
            }
        }

        return array_slice($results, 0, 5);
    }

    public function navigateTo(string $url): void
    {
        $this->close();
        $this->dispatch('navigate', url: $url);
    }

    protected function resolveIconName($icon): string
    {
        if ($icon instanceof \BackedEnum) {
            return $icon->value ?? 'heroicon-o-rectangle-stack';
        }
        if (is_string($icon)) {
            if (str_starts_with($icon, 'heroicon-')) {
                return $icon;
            }
        }
        return 'heroicon-o-rectangle-stack';
    }

    public function incrementIndex(): void
    {
        $filtered = array_filter($this->results, fn($r) => $r['type'] !== 'header');
        $count = count($filtered);
        if ($count === 0) return;
        $this->selectedIndex = ($this->selectedIndex + 1) % $count;
    }

    public function decrementIndex(): void
    {
        $filtered = array_filter($this->results, fn($r) => $r['type'] !== 'header');
        $count = count($filtered);
        if ($count === 0) return;
        $this->selectedIndex = ($this->selectedIndex - 1 + $count) % $count;
    }

    public function selectCurrent(): void
    {
        $filtered = array_values(array_filter($this->results, fn($r) => $r['type'] !== 'header'));
        if (isset($filtered[$this->selectedIndex])) {
            $url = $filtered[$this->selectedIndex]['url'];
            $this->dispatch('navigate', url: $url);
            $this->close();
        }
    }

    public function render()
    {
        return view('livewire.command-palette');
    }
}
