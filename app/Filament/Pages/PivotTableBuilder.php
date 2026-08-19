<?php

namespace App\Filament\Pages;

use App\Services\PivotTableService;
use Filament\Pages\Page;
use Illuminate\Support\Str;

class PivotTableBuilder extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-table-cells';

    protected static ?int $navigationSort = 402;

    protected string $view = 'filament.pages.pivot-table-builder';

    protected static ?string $title = 'Pivot Table Builder';

    protected static ?string $navigationLabel = 'Pivot Table Builder';

    protected static ?string $slug = 'pivot-table-builder';

    public array $dataSources = [];
    public ?string $source = null;
    public array $fields = ['dimensions' => [], 'measures' => []];
    public array $dimensions = [];
    public array $measures = [];
    public array $filters = [];
    public ?array $result = null;
    public string $reportName = '';
    public array $savedReports = [];
    public array $aggregateOptions = [];
    public array $filterOperators = [];

    public static function getNavigationGroup(): ?string
    {
        return \App\Filament\Navigation\NavigationGroup::REPORTS->value;
    }

    public function mount(): void
    {
        $service = $this->service();

        $this->dataSources = collect($service->getDataSources())
            ->map(fn($source, $key) => [
                'key' => $key,
                'label' => $source['label'],
            ])
            ->values()
            ->toArray();

        $this->aggregateOptions = $service->getAggregates();
        $this->filterOperators = $service->getFilterOperators();

        $this->savedReports = $service->getSavedReports()
            ->map(fn($report) => [
                'id' => $report->id,
                'name' => $report->name,
                'config' => $report->pivot_config ?? [],
            ])
            ->toArray();
    }

    public function updatedSource(?string $value): void
    {
        $this->dimensions = [];
        $this->measures = [];
        $this->filters = [];
        $this->result = null;

        if ($value) {
            $this->fields = $this->service()->getFields($value);
        } else {
            $this->fields = ['dimensions' => [], 'measures' => []];
        }
    }

    public function addDimension(?string $field): void
    {
        if ($field && !in_array($field, $this->dimensions, true)) {
            $this->dimensions[] = $field;
        }
    }

    public function removeDimension(string $field): void
    {
        $this->dimensions = array_values(array_diff($this->dimensions, [$field]));
    }

    public function reorderDimensions(array $order): void
    {
        $filtered = array_values(array_filter($order, fn($f) => in_array($f, $this->dimensions, true)));

        foreach ($this->dimensions as $field) {
            if (!in_array($field, $filtered, true)) {
                $filtered[] = $field;
            }
        }

        $this->dimensions = $filtered;
    }

    public function addMeasure(): void
    {
        $this->measures[] = ['field' => null, 'aggregate' => 'sum'];
    }

    public function removeMeasure(int $idx): void
    {
        unset($this->measures[$idx]);
        $this->measures = array_values($this->measures);
    }

    public function reorderMeasures(array $order): void
    {
        $filtered = array_values(array_filter($order, fn($i) => isset($this->measures[$i])));

        $remaining = array_diff_key($this->measures, array_flip($filtered));
        $this->measures = array_merge(
            array_map(fn($i) => $this->measures[$i], $filtered),
            array_values($remaining)
        );
    }

    public function addFilter(): void
    {
        $this->filters[] = [
            'column' => null,
            'operator' => '=',
            'value' => null,
            'value_end' => null,
        ];
    }

    public function removeFilter(int $idx): void
    {
        unset($this->filters[$idx]);
        $this->filters = array_values($this->filters);
    }

    public function preview(): void
    {
        if (!$this->source) {
            $this->result = ['error' => 'Pilih sumber data terlebih dahulu.'];
            return;
        }

        try {
            $this->result = $this->service()->executePivot(
                $this->source,
                array_values($this->dimensions),
                array_values($this->measures),
                array_values($this->filters),
            );
        } catch (\Throwable $e) {
            $this->result = ['error' => $e->getMessage()];
        }
    }

    public function saveReport(): void
    {
        $this->validate([
            'reportName' => 'required|string|max:255',
            'source' => 'required|string',
        ]);

        $report = $this->service()->saveReport($this->reportName, $this->source, [
            'dimensions' => array_values($this->dimensions),
            'measures' => array_values($this->measures),
            'filters' => array_values($this->filters),
        ]);

        $this->savedReports = $this->service()->getSavedReports()
            ->map(fn($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'config' => $r->pivot_config ?? [],
            ])
            ->toArray();

        $this->reportName = '';

        $this->dispatch('notify', type: 'success', message: "Laporan \"{$report->name}\" berhasil disimpan.");
    }

    public function loadReport(int $id): void
    {
        $report = collect($this->savedReports)->firstWhere('id', $id);

        if (!$report) {
            return;
        }

        $config = $report['config'] ?? [];
        $this->source = $config['source'] ?? null;
        $this->dimensions = $config['dimensions'] ?? [];
        $this->measures = $config['measures'] ?? [];
        $this->filters = $config['filters'] ?? [];
        $this->reportName = $report['name'];

        $this->fields = $this->source
            ? $this->service()->getFields($this->source)
            : ['dimensions' => [], 'measures' => []];

        $this->preview();
    }

    public function exportCsv()
    {
        if (!$this->source) {
            $this->dispatch('notify', type: 'warning', message: 'Pilih sumber data terlebih dahulu.');
            return;
        }

        try {
            $result = $this->service()->executePivot(
                $this->source,
                array_values($this->dimensions),
                array_values($this->measures),
                array_values($this->filters),
            );
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'danger', message: $e->getMessage());
            return;
        }

        $csv = $this->service()->exportCsv($result);
        $fileName = Str::slug($this->reportName ?: 'pivot') . '_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(
            fn() => print($csv),
            $fileName,
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    protected function service(): PivotTableService
    {
        return app(PivotTableService::class);
    }
}
