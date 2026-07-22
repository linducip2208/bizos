<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class AdvancedReportBuilder extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?int $navigationSort = 1301;

    protected static string $view = 'filament.pages.advanced-report-builder';

    protected static ?string $title = 'Report Builder';

    protected static ?string $navigationLabel = 'Report Builder';

    protected static ?string $slug = 'advanced-report-builder';

    public array $reportConfig = [
        'name' => '',
        'type' => 'pivot',
        'row_fields' => [],
        'column_fields' => [],
        'values' => [],
        'filters' => [],
    ];

    public ?string $selectedTable = null;
    public ?array $result = null;

    public array $availableTables = [];

    public static function getNavigationGroup(): ?string
    {
        return '🐞 Debug';
    }

    public function mount(): void
    {
        $this->availableTables = [
            ['table' => 'employees', 'label' => 'Employees'],
            ['table' => 'invoices', 'label' => 'Invoices'],
            ['table' => 'clients', 'label' => 'Clients'],
            ['table' => 'deals', 'label' => 'Deals'],
        ];
    }

    public function runReport(): void
    {
        $this->result = [
            'columns' => [],
            'rows' => [],
            'message' => 'Fitur report builder dalam pengembangan.',
        ];
    }

    public function saveReportAction(): void
    {
    }

    public function exportExcel(): void
    {
    }

    public function exportPdf(): void
    {
    }

    public function addRowField(): void
    {
    }

    public function removeRowField(string $field): void
    {
    }

    public function addColumnField(): void
    {
    }

    public function removeColumnField(string $field): void
    {
    }

    public function addValueField(): void
    {
    }

    public function removeValueField(int $idx): void
    {
    }

    public function addFilter(): void
    {
    }

    public function removeFilter(int $idx): void
    {
    }
}
