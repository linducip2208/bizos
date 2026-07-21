<?php

namespace App\Filament\Resources\StockBalances\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StockBalanceTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('variant.name')
                    ->label('Varian')
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('warehouse.name')
                    ->label('Gudang')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Kuantitas')
                    ->numeric(2)
                    ->sortable()
                    ->color(fn ($state): string => $state <= 0 ? 'danger' : ($state < 10 ? 'warning' : 'success')),
                TextColumn::make('average_cost')
                    ->label('Biaya Rata-rata')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('last_cost')
                    ->label('Biaya Terakhir')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('product_id', 'asc')
            ->filters([
                SelectFilter::make('product_id')
                    ->label('Produk')
                    ->relationship('product', 'name'),
                SelectFilter::make('warehouse_id')
                    ->label('Gudang')
                    ->relationship('warehouse', 'name'),
            ])
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