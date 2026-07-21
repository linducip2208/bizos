<?php

namespace App\Filament\Resources\Assets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('asset_code')
                    ->label('Kode Aset')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('acquisition_date')
                    ->label('Tanggal Perolehan')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('acquisition_cost')
                    ->label('Harga Perolehan')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('current_value')
                    ->label('Nilai Saat Ini')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'active' => 'Aktif',
                        'maintenance' => 'Dalam Perbaikan',
                        'disposed' => 'Dijual/Dihapus',
                        'idle' => 'Menganggur',
                        'transferred' => 'Dipindahkan',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'active' => 'success',
                        'maintenance' => 'warning',
                        'disposed' => 'danger',
                        'idle' => 'gray',
                        'transferred' => 'info',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('asset_code', 'asc')
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