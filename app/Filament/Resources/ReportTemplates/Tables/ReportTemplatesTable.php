<?php

namespace App\Filament\Resources\ReportTemplates\Tables;

use App\Services\ReportBuilderService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReportTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Template')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'sales' => 'Penjualan',
                        'finance' => 'Keuangan',
                        'hrm' => 'HRM',
                        'inventory' => 'Inventaris',
                        'custom' => 'Kustom',
                        default => ucfirst($state),
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'sales' => 'success',
                        'finance' => 'warning',
                        'hrm' => 'info',
                        'inventory' => 'primary',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('query_type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'table' => 'Tabel',
                        'chart' => 'Grafik',
                        'summary' => 'Ringkasan',
                        default => ucfirst($state),
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'table' => 'gray',
                        'chart' => 'success',
                        'summary' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('is_system')
                    ->label('Sistem')
                    ->badge()
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Ya' : 'Tidak')
                    ->color(fn(bool $state): string => $state ? 'primary' : 'gray'),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn($record) => 'Preview: ' . $record->name)
                    ->modalContent(function ($record) {
                        try {
                            $service = app(ReportBuilderService::class);
                            $data = $service->execute($record);
                            $chartData = null;
                            if (in_array($record->query_type, ['chart', 'summary'])) {
                                $chartData = $service->generateChartData($record);
                            }

                            return view('filament.reports.preview-modal', [
                                'template' => $record,
                                'data' => $data,
                                'chartData' => $chartData,
                            ])->render();
                        } catch (\Exception $e) {
                            return '<div class="p-4 text-danger-500">Error: ' . e($e->getMessage()) . '</div>';
                        }
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
                Action::make('export_excel')
                    ->label('Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function ($record) {
                        $service = app(ReportBuilderService::class);
                        return $service->exportToExcel($record);
                    }),
                Action::make('export_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('danger')
                    ->action(function ($record) {
                        $service = app(ReportBuilderService::class);
                        return $service->exportToPdf($record);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}