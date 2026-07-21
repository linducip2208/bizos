<?php

namespace App\Filament\Resources\LabOrders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class LabOrderTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('order_date', 'desc')
            ->columns([
                TextColumn::make('order_date')
                    ->label('Tgl Order')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('patient.full_name')
                    ->label('Pasien')
                    ->searchable(),
                TextColumn::make('doctor.first_name')
                    ->label('Dokter')
                    ->formatStateUsing(fn ($record) => $record->doctor?->first_name . ' ' . $record->doctor?->last_name)
                    ->searchable(),
                TextColumn::make('lab_type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'hematology' => 'Hematologi',
                        'chemistry' => 'Kimia',
                        'microbiology' => 'Mikrobiologi',
                        'radiology' => 'Radiologi',
                        'urine' => 'Urinalisis',
                        'other' => 'Lainnya',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'hematology' => 'danger',
                        'chemistry' => 'info',
                        'microbiology' => 'warning',
                        'radiology' => 'primary',
                        'urine' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('results_count')
                    ->label('Hasil')
                    ->counts('results')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'ordered' => 'Diorder',
                        'sample_collected' => 'Sampel Diambil',
                        'in_progress' => 'Diproses',
                        'completed' => 'Selesai',
                        'reviewed' => 'Direview',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'ordered' => 'gray',
                        'sample_collected' => 'info',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'reviewed' => 'primary',
                        default => 'gray',
                    }),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}