<?php

namespace App\Filament\Resources\AdvancedReports\Pages;

use App\Filament\Resources\AdvancedReports\AdvancedReportResource;
use App\Models\AdvancedReport;
use App\Services\AdvancedBiService;
use Filament\Actions;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class AdvancedReportBuilder extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = AdvancedReportResource::class;

    protected static ?string $title = 'Advanced BI Report Builder';

    protected static ?string $navigationLabel = 'Report Builder';

    protected static ?string $slug = 'builder';

    protected string $view = 'filament.pages.advanced-report-builder';

    public ?array $reportConfig = [];
    public ?array $result = null;
    public ?string $selectedTable = null;
    public array $availableTables = [];
    public array $tableColumns = [];
    public array $reportTypes = [
        'pivot' => 'Pivot Table',
        'crosstab' => 'Cross Tab Analysis',
        'table' => 'Tabel Data',
    ];
    public array $aggregateOptions = [];

    public function mount(?int $record = null): void
    {
        $service = app(AdvancedBiService::class);

        if ($record) {
            $report = AdvancedReport::findOrFail($record);
            $this->reportConfig = [
                'name' => $report->name,
                'type' => $report->type,
                'source_table' => $report->data_source['table_name'] ?? null,
                'rows' => $report->row_fields ?? [],
                'columns' => $report->column_fields ?? [],
                'values' => $report->value_fields ?? [],
                'filters' => $report->filters ?? [],
            ];
            $this->selectedTable = $this->reportConfig['source_table'] ?? null;
        } else {
            $this->reportConfig = [
                'name' => 'Laporan Baru',
                'type' => 'pivot',
                'rows' => [],
                'columns' => [],
                'values' => [],
                'filters' => [],
            ];
        }

        $this->availableTables = $service->getAvailableTables();
        $this->aggregateOptions = $service->getAggregates();

        if ($this->selectedTable) {
            $this->loadTableColumns($this->selectedTable);
        }
    }

    public function loadTableColumns(?string $tableName): void
    {
        if ($tableName) {
            foreach ($this->availableTables as $t) {
                if ($t['table'] === $tableName) {
                    $this->tableColumns = $t['columns'];
                    break;
                }
            }
        }
    }

    public function updatedSelectedTable(string $table): void
    {
        $this->loadTableColumns($table);
        $this->reportConfig['source_table'] = $table;
    }

    public function addRowField(string $field): void
    {
        if (!in_array($field, $this->reportConfig['rows'] ?? [])) {
            $this->reportConfig['rows'][] = $field;
        }
    }

    public function removeRowField(string $field): void
    {
        $this->reportConfig['rows'] = array_values(
            array_filter($this->reportConfig['rows'] ?? [], fn($f) => $f !== $field)
        );
    }

    public function addColumnField(string $field): void
    {
        if (!in_array($field, $this->reportConfig['columns'] ?? [])) {
            $this->reportConfig['columns'][] = $field;
        }
    }

    public function removeColumnField(string $field): void
    {
        $this->reportConfig['columns'] = array_values(
            array_filter($this->reportConfig['columns'] ?? [], fn($f) => $f !== $field)
        );
    }

    public function addValueField(): void
    {
        $this->reportConfig['values'][] = [
            'field' => '',
            'aggregate' => 'sum',
            'alias' => '',
        ];
    }

    public function removeValueField(int $index): void
    {
        if (isset($this->reportConfig['values'][$index])) {
            unset($this->reportConfig['values'][$index]);
            $this->reportConfig['values'] = array_values($this->reportConfig['values']);
        }
    }

    public function addFilter(): void
    {
        $this->reportConfig['filters'][] = [
            'column' => '',
            'operator' => '=',
            'value' => '',
            'type' => 'where',
        ];
    }

    public function removeFilter(int $index): void
    {
        if (isset($this->reportConfig['filters'][$index])) {
            unset($this->reportConfig['filters'][$index]);
            $this->reportConfig['filters'] = array_values($this->reportConfig['filters']);
        }
    }

    public function runReport(): void
    {
        try {
            $service = app(AdvancedBiService::class);
            $config = $this->reportConfig;

            if (empty($config['source_table'])) {
                Notification::make()->title('Pilih tabel sumber')->danger()->send();
                return;
            }

            $result = match ($config['type']) {
                'crosstab' => $service->buildCrossTab(
                    $config['source_table'],
                    $config['rows'][0] ?? '',
                    $config['columns'][0] ?? '',
                    $config['values'][0]['field'] ?? '',
                    $config['values'][0]['aggregate'] ?? 'sum'
                ),
                default => $service->buildPivotTable($config),
            };

            $this->result = $result;

            Notification::make()->title('Laporan berhasil digenerate')->success()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
        }
    }

    public function saveReportAction(): void
    {
        $this->runReport();
        $this->saveReport();
    }

    public function saveReport(): void
    {
        try {
            $service = app(AdvancedBiService::class);
            $config = $this->reportConfig;
            $config['data_source'] = [
                'table_name' => $this->selectedTable,
            ];

            $report = $service->saveReport(
                $config['name'] ?? 'Laporan Tanpa Nama',
                $config
            );

            Notification::make()
                ->title('Laporan disimpan')
                ->body("Laporan '{$report->name}' berhasil disimpan.")
                ->success()
                ->send();

        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal menyimpan: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function exportExcel(): void
    {
        try {
            $service = app(AdvancedBiService::class);
            $config = $this->reportConfig;
            $config['data_source'] = ['table_name' => $this->selectedTable];

            $report = AdvancedReport::make($config);
            $service->exportToExcelAdvanced($report);
        } catch (\Throwable $e) {
            Notification::make()->title('Export error: ' . $e->getMessage())->danger()->send();
        }
    }

    public function exportPdf(): void
    {
        try {
            $service = app(AdvancedBiService::class);
            $config = $this->reportConfig;
            $config['data_source'] = ['table_name' => $this->selectedTable];

            $report = AdvancedReport::make($config);
            $service->exportToPdfAdvanced($report);
        } catch (\Throwable $e) {
            Notification::make()->title('Export error: ' . $e->getMessage())->danger()->send();
        }
    }

    public function getTitle(): string
    {
        return 'Advanced BI Report Builder';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
