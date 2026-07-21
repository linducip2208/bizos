<?php

namespace App\Filament\Resources\Batches\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BatchTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('batch_number')
                    ->label('Nomor Batch')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Kuantitas')
                    ->numeric(decimalPlaces: 4)
                    ->sortable(),
                TextColumn::make('unit_cost')
                    ->label('Biaya Unit')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('warehouse.name')
                    ->label('Gudang')
                    ->sortable(),
                TextColumn::make('manufacturing_date')
                    ->label('Tanggal Produksi')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('expiry_date')
                    ->label('Kedaluwarsa')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn ($record) => $record->expiry_date && $record->expiry_date->isPast() ? 'danger' : null),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->defaultSort('created_at', 'desc');
    }
}
