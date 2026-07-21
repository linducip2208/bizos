<?php

namespace App\Filament\Resources\AssetCategory\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssetCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label('Perusahaan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama Kategori')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('depreciation_method')
                    ->label('Metode Penyusutan')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'straight_line' => 'Garis Lurus',
                        'declining_balance' => 'Saldo Menurun',
                        'sum_of_years' => 'Jumlah Tahun',
                        'units_of_production' => 'Unit Produksi',
                        'none' => 'Tanpa',
                        default => 'Tidak Diketahui',
                    }),
                TextColumn::make('useful_life_years')
                    ->label('Masa Manfaat')
                    ->sortable()
                    ->numeric()
                    ->suffix(' tahun'),
                TextColumn::make('salvage_value_percent')
                    ->label('Nilai Residu')
                    ->formatStateUsing(fn ($state): string => $state . '%')
                    ->sortable(),
            ])
            ->defaultSort('name', 'asc')
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
