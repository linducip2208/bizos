<?php

namespace App\Http\Livewire;

use App\Services\EnterpriseSearchService;
use Livewire\Component;

class GlobalSearchBar extends Component
{
    public string $query = '';
    public array $results = [];
    public array $suggestions = [];
    public array $popularSearches = [];
    public bool $isOpen = false;
    public int $selectedIndex = 0;
    public int $totalHits = 0;
    public float $searchTimeMs = 0;
    public array $activeFilters = [];
    public array $availableModules = [
        'hrm' => 'HRM',
        'crm' => 'CRM',
        'finance' => 'Finance',
        'helpdesk' => 'Helpdesk',
        'inventory' => 'Inventori',
        'project' => 'Proyek',
        'kolaborasi' => 'Kolaborasi',
    ];

    protected $listeners = [
        'openGlobalSearch' => 'open',
        'closeGlobalSearch' => 'close',
    ];

    public function mount(): void
    {
        $this->loadPopularSearches();
    }

    public function open(): void
    {
        $this->isOpen = true;
        $this->query = '';
        $this->results = [];
        $this->selectedIndex = 0;
        $this->activeFilters = [];
        $this->loadPopularSearches();
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->query = '';
        $this->results = [];
        $this->suggestions = [];
        $this->selectedIndex = 0;
    }

    public function updatedQuery(): void
    {
        $this->selectedIndex = 0;
        if (mb_strlen(trim($this->query)) >= 2) {
            $this->performSearch();
        } else {
            $this->results = [];
            $this->suggestions = [];
            $this->totalHits = 0;
        }
    }

    public function toggleFilter(string $module): void
    {
        if (in_array($module, $this->activeFilters)) {
            $this->activeFilters = array_values(array_diff($this->activeFilters, [$module]));
        } else {
            $this->activeFilters[] = $module;
        }
        if (mb_strlen(trim($this->query)) >= 2) {
            $this->performSearch();
        }
    }

    public function setQuery(string $q): void
    {
        $this->query = $q;
        $this->performSearch();
    }

    protected function performSearch(): void
    {
        $service = app(EnterpriseSearchService::class);

        $filters = [];
        if (!empty($this->activeFilters)) {
            $filters['module'] = $this->activeFilters;
        }

        $result = $service->search($this->query, $filters, 1, 20);

        $this->results = $result['results'];
        $this->totalHits = $result['total_hits'];
        $this->searchTimeMs = $result['search_time_ms'];

        if (mb_strlen(trim($this->query)) >= 2) {
            $this->suggestions = $service->suggest($this->query, 5);
        }
    }

    public function loadPopularSearches(): void
    {
        $service = app(EnterpriseSearchService::class);
        $this->popularSearches = array_slice($service->getPopularSearches(8), 0, 8);
    }

    public function incrementIndex(): void
    {
        if (count($this->results) === 0) return;
        $this->selectedIndex = ($this->selectedIndex + 1) % count($this->results);
    }

    public function decrementIndex(): void
    {
        if (count($this->results) === 0) return;
        $this->selectedIndex = ($this->selectedIndex - 1 + count($this->results)) % count($this->results);
    }

    public function selectCurrent(): void
    {
        if (isset($this->results[$this->selectedIndex])) {
            $url = $this->results[$this->selectedIndex]['url'] ?? '#';

            $service = app(EnterpriseSearchService::class);
            $service->logClick(
                $this->query,
                auth()->id(),
                $this->results[$this->selectedIndex]['module'] ?? '',
                $this->results[$this->selectedIndex]['model_class'] ?? '',
                $this->results[$this->selectedIndex]['id'] ?? 0,
            );

            $this->dispatch('navigate', url: $url);
            $this->close();
        }
    }

    public function navigateTo(string $url): void
    {
        $this->close();
        $this->dispatch('navigate', url: $url);
    }

    protected function getModuleColor(string $module): string
    {
        return match ($module) {
            'hrm' => 'indigo',
            'crm' => 'blue',
            'finance' => 'emerald',
            'helpdesk' => 'amber',
            'inventory' => 'purple',
            'project' => 'rose',
            'kolaborasi' => 'teal',
            default => 'gray',
        };
    }

    protected function getModelLabel(string $model): string
    {
        return match ($model) {
            'Employee' => 'Karyawan',
            'Client' => 'Klien',
            'Lead' => 'Lead',
            'Deal' => 'Deal',
            'Invoice' => 'Faktur',
            'Ticket' => 'Tiket',
            'Product' => 'Produk',
            'Project' => 'Proyek',
            'Task' => 'Tugas',
            'ChatMessage' => 'Chat',
            'Meeting' => 'Rapat',
            'WikiPage' => 'Wiki',
            'DocumentTemplate' => 'Dokumen',
            'ServiceContract' => 'Kontrak',
            'Asset' => 'Aset',
            default => $model,
        };
    }

    public function render()
    {
        return view('livewire.global-search-bar', [
            'moduleColor' => fn($m) => $this->getModuleColor($m),
            'modelLabel' => fn($m) => $this->getModelLabel($m),
        ]);
    }
}
