<?php

namespace App\Filament\Resources\StockOpnameItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StockOpnameItemTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('stockOpname.opname_number')
                    ->label('No. Opname')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('system_quantity')
                    ->label('Qty Sistem')
                    ->numeric(2)
                    ->sortable(),
                TextColumn::make('physical_quantity')
                    ->label('Qty Fisik')
                    ->numeric(2)
                    ->sortable(),
                TextColumn::make('difference')
                    ->label('Selisih')
                    ->numeric(2)
                    ->sortable()
                    ->color(fn ($state) => $state < 0 ? 'danger' : ($state > 0 ? 'warning' : 'success')),
                TextColumn::make('unit_cost')
                    ->label('Harga Satuan')
                    ->money('IDR')
                    ->sortable()
                    ->placeholder('-'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('stock_opname_id')
                    ->label('Opname')
                    ->relationship('stockOpname', 'opname_number'),
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
