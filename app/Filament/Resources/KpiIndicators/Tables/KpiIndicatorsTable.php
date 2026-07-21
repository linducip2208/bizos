<?php

namespace App\Filament\Resources\KpiIndicators\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KpiIndicatorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('template.name')
                    ->label('Template')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama Indikator')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'financial' => 'success',
                        'customer' => 'primary',
                        'internal_process' => 'warning',
                        'learning_growth' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'financial' => 'Keuangan',
                        'customer' => 'Pelanggan',
                        'internal_process' => 'Proses Internal',
                        'learning_growth' => 'Pembelajaran',
                        default => $state,
                    }),
                TextColumn::make('weight_percent')
                    ->label('Bobot')
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('target_type')
                    ->label('Tipe Target')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'numeric' => 'Numerik',
                        'percentage' => 'Persentase',
                        'boolean' => 'Ya/Tidak',
                        'rating_1_5' => 'Rating 1-5',
                        default => $state,
                    }),
                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),
            ])
            ->defaultSort('template_id')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
