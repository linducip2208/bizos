<?php

namespace App\Filament\Concerns;

use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

trait HasBulkActions
{
    protected function getTableBulkActions(): array
    {
        return array_merge(
            $this->getCustomBulkActions(),
            [
                BulkAction::make('export_selected')
                    ->label('Export Terpilih')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(fn (Collection $records) => $this->exportSelectedToExcel($records)),

                BulkAction::make('change_status')
                    ->label('Ubah Status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->form($this->getChangeStatusFormSchema())
                    ->action(fn (Collection $records, array $data) => $this->bulkChangeStatus($records, $data))
                    ->deselectRecordsAfterCompletion(),

                BulkAction::make('delete_selected')
                    ->label('Hapus Terpilih')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Data Terpilih')
                    ->modalDescription('Apakah Anda yakin ingin menghapus semua data yang dipilih? Tindakan ini tidak dapat dibatalkan.')
                    ->modalSubmitActionLabel('Ya, Hapus')
                    ->action(fn (Collection $records) => $this->bulkDelete($records))
                    ->deselectRecordsAfterCompletion(),
            ],
        );
    }

    protected function getCustomBulkActions(): array
    {
        return [];
    }

    protected function getChangeStatusFormSchema(): array
    {
        return [
            \Filament\Forms\Components\Select::make('status')
                ->label('Status Baru')
                ->options($this->getStatusOptions())
                ->required(),
        ];
    }

    protected function getStatusOptions(): array
    {
        return [
            'active' => 'Aktif',
            'inactive' => 'Nonaktif',
            'draft' => 'Draft',
            'published' => 'Dipublikasi',
            'archived' => 'Arsip',
        ];
    }

    protected function bulkChangeStatus(Collection $records, array $data): void
    {
        $records->each(function ($record) use ($data) {
            if (in_array('status', $record->getFillable())) {
                $record->update(['status' => $data['status']]);
            }
        });
    }

    protected function bulkDelete(Collection $records): void
    {
        $records->each(function ($record) {
            if (method_exists($record, 'delete')) {
                $record->delete();
            }
        });
    }

    protected function exportSelectedToExcel(Collection $records): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $columns = $this->getBulkExportColumns();
        $filename = $this->getBulkExportFilename();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $col = 'A';
        foreach ($columns as $label) {
            $cell = $sheet->getCell("{$col}1");
            $cell->setValue($label);
            $sheet->getStyle("{$col}1")->applyFromArray([
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
            ]);
            $col++;
        }

        $row = 2;
        foreach ($records as $record) {
            $col = 'A';
            foreach ($columns as $field => $label) {
                $value = data_get($record, $field, '');

                if ($value instanceof \DateTimeInterface) {
                    $value = $value->format('Y-m-d H:i:s');
                } elseif (is_bool($value)) {
                    $value = $value ? 'Ya' : 'Tidak';
                } elseif (is_array($value)) {
                    $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                }

                $sheet->getCell("{$col}{$row}")->setValue((string) $value);
                $col++;
            }
            $row++;
        }

        $lastCol = chr(ord('A') + count($columns) - 1);
        $sheet->getStyle("A1:{$lastCol}" . ($row - 1))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E5E7EB'],
                ],
            ],
        ]);

        foreach (range('A', $lastCol) as $colLetter) {
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $dir = storage_path('app/exports');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filePath = $dir . DIRECTORY_SEPARATOR . $filename;
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return response()->download($filePath, $filename)->deleteFileAfterSend();
    }

    protected function getBulkExportColumns(): array
    {
        $resource = static::getResource();
        $model = $resource::getModel();
        $instance = new $model();
        $columns = [];

        foreach ($instance->getFillable() as $field) {
            $columns[$field] = ucwords(str_replace('_', ' ', $field));
        }

        return $columns;
    }

    protected function getBulkExportFilename(): string
    {
        $resourceClass = static::getResource();
        $name = class_basename($resourceClass);
        $name = str_replace('Resource', '', $name);

        return $name . '_Selected_' . now()->format('Ymd_His') . '.xlsx';
    }
}
