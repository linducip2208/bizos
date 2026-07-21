<?php

namespace App\Filament\Resources\FieldService\VanInventoryResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VanInventoryTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('van.license_plate')
                    ->label('Van')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->sortable()
                    ->color(fn ($record) => $record->quantity <= $record->reorder_point ? 'danger' : null),
                TextColumn::make('min_quantity')
                    ->label('Minimal'),
                TextColumn::make('reorder_point')
                    ->label('Reorder Point'),
                TextColumn::make('last_restock_date')
                    ->label('Restock Terakhir')
                    ->date('d M Y')
                    ->sortable(),
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