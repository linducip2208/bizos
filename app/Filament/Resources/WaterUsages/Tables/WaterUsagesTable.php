<?php

namespace App\Filament\Resources\WaterUsages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WaterUsagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('record_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('source')
                    ->label('Sumber')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'municipal' => 'PDAM',
                        'well' => 'Sumur',
                        'rainwater' => 'Air Hujan',
                        'recycled' => 'Daur Ulang',
                        'surface_water' => 'Air Permukaan',
                        default => $state,
                    })
                    ->badge()
                    ->sortable(),
                TextColumn::make('quantity_m3')
                    ->label('Jumlah (m3)')
                    ->numeric(2)
                    ->sortable(),
                TextColumn::make('purpose')
                    ->label('Tujuan')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'production' => 'Produksi',
                        'sanitation' => 'Sanitasi',
                        'cooling' => 'Pendingin',
                        'irrigation' => 'Irigasi',
                        'domestic' => 'Domestik',
                        default => $state,
                    })
                    ->badge(),
                TextColumn::make('cost')
                    ->label('Biaya')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('is_recycled')
                    ->label('Daur Ulang')
                    ->formatStateUsing(fn($state) => $state ? 'Ya' : 'Tidak')
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'gray'),
                TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable(),
            ])
            ->defaultSort('record_date', 'desc')
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