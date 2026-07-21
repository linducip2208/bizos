<?php

namespace App\Filament\Concerns;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

trait HasExcelExport
{
    public function getHeaderActions(): array
    {
        return array_merge(
            $this->getCustomHeaderActions(),
            [
                Action::make('export_excel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(fn () => $this->exportToExcel()),
            ],
        );
    }

    protected function getCustomHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function exportToExcel(): BinaryFileResponse
    {
        $query = $this->getFilteredSortedTableQuery();
        $columns = $this->getExportColumns();
        $filename = $this->getExportFilename();

        $total = $query->count();

        if ($total > 1000) {
            return $this->exportLargeDataset($query, $columns, $filename, $total);
        }

        return $this->generateExcel($query->get(), $columns, $filename);
    }

    protected function exportLargeDataset(Builder $query, array $columns, string $filename, int $total): BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $col = 'A';
        $headerStyle = $this->getExcelHeaderStyle();
        foreach ($columns as $label) {
            $cell = $sheet->getCell("{$col}1");
            $cell->setValue($label);
            $sheet->getStyle("{$col}1")->applyFromArray($headerStyle);
            $col++;
        }

        $row = 2;
        $query->chunk(500, function (Collection $records) use ($sheet, $columns, &$row) {
            foreach ($records as $record) {
                $col = 'A';
                foreach ($columns as $field => $label) {
                    $value = $this->resolveExportValue($record, $field);
                    $sheet->getCell("{$col}{$row}")->setValue($value);
                    $col++;
                }
                $row++;
            }
        });

        $lastCol = chr(ord('A') + count($columns) - 1);
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray($this->getExcelHeaderStyle());
        $sheet->getStyle("A1:{$lastCol}" . ($row - 1))->applyFromArray($this->getExcelBodyStyle());

        foreach (range('A', $lastCol) as $colLetter) {
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $filePath = $this->getTempExportPath($filename);

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return response()->download($filePath, $filename)->deleteFileAfterSend();
    }

    protected function generateExcel(Collection $records, array $columns, string $filename): BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $col = 'A';
        $headerStyle = $this->getExcelHeaderStyle();
        foreach ($columns as $label) {
            $cell = $sheet->getCell("{$col}1");
            $cell->setValue($label);
            $sheet->getStyle("{$col}1")->applyFromArray($headerStyle);
            $col++;
        }

        $row = 2;
        foreach ($records as $record) {
            $col = 'A';
            foreach ($columns as $field => $label) {
                $value = $this->resolveExportValue($record, $field);
                $sheet->getCell("{$col}{$row}")->setValue($value);
                $col++;
            }
            $row++;
        }

        $lastCol = chr(ord('A') + count($columns) - 1);
        $sheet->getStyle("A1:{$lastCol}" . ($row - 1))->applyFromArray($this->getExcelBodyStyle());

        foreach (range('A', $lastCol) as $colLetter) {
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $filePath = $this->getTempExportPath($filename);

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return response()->download($filePath, $filename)->deleteFileAfterSend();
    }

    protected function resolveExportValue($record, string $field): string
    {
        if ($record instanceof \Illuminate\Database\Eloquent\Model) {
            if ($record->isRelation($field) || str_contains($field, '.')) {
                $value = data_get($record, $field);
            } elseif ($record->hasCast($field)) {
                $value = $record->getAttribute($field);
            } else {
                $value = $record->{$field} ?? '';
            }
        } else {
            $value = data_get($record, $field, '');
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if ($value instanceof \Illuminate\Support\Carbon) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_bool($value)) {
            return $value ? 'Ya' : 'Tidak';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return (string) $value;
    }

    public function getFilteredSortedTableQuery(): Builder
    {
        $table = $this->getResourceTable();

        if (method_exists($table, 'getQuery')) {
            $query = $table->getQuery();
        } else {
            $model = static::getResource()::getModel();
            $query = $model::query();
        }

        if (method_exists($this, 'applyFiltersToTableQuery')) {
            $query = $this->applyFiltersToTableQuery($query);
        }

        return $query;
    }

    protected function getExportColumns(): array
    {
        $table = $this->getResourceTable();

        if (method_exists($table, 'getColumns')) {
            $columns = [];
            foreach ($table->getColumns() as $column) {
                $name = method_exists($column, 'getName') ? $column->getName() : null;
                $label = method_exists($column, 'getLabel') ? $column->getLabel() : ($name ?? '');
                if ($name) {
                    $columns[$name] = $label;
                }
            }
            return $columns;
        }

        $model = static::getResource()::getModel();
        $instance = new $model();
        $columns = [];
        foreach ($instance->getFillable() as $field) {
            $columns[$field] = ucwords(str_replace('_', ' ', $field));
        }
        return $columns;
    }

    protected function getExportFilename(): string
    {
        $resourceClass = static::getResource();
        $name = class_basename($resourceClass);
        $name = str_replace('Resource', '', $name);

        return $name . '_' . now()->format('Ymd_His') . '.xlsx';
    }

    protected function getExcelHeaderStyle(): array
    {
        return [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ];
    }

    protected function getExcelBodyStyle(): array
    {
        return [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E5E7EB'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
    }

    protected function getTempExportPath(string $filename): string
    {
        $dir = storage_path('app/exports');

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir . DIRECTORY_SEPARATOR . $filename;
    }

    protected function getResourceTable(): mixed
    {
        $resource = static::getResource();

        if (method_exists($resource, 'table')) {
            return app()->call([$resource, 'table'], [
                'table' => app(\Filament\Tables\Table::class),
            ]);
        }

        return null;
    }
}
