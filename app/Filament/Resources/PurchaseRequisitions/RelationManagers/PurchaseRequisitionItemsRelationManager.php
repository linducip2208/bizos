<?php

namespace App\Filament\Resources\PurchaseRequisitions\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PurchaseRequisitionItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'Item Permintaan Pembelian';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('item_name')
                    ->label('Nama Item')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('specification')
                    ->label('Spesifikasi')
                    ->limit(30)
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('unit')
                    ->label('Satuan'),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Kuantitas')
                    ->numeric(2),
                Tables\Columns\TextColumn::make('estimated_price')
                    ->label('Estimasi Harga')
                    ->money('IDR')
                    ->placeholder('-'),
            ])
            ->defaultSort('id', 'asc')
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}