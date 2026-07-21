<?php

namespace App\Filament\Resources\GoodsReceiptItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GoodsReceiptItemTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('goodsReceipt.grn_number')
                    ->label('No. GRN')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('item_name')
                    ->label('Nama Item')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('unit')
                    ->label('Satuan'),
                TextColumn::make('quantity_received')
                    ->label('Qty Diterima')
                    ->numeric(2)
                    ->sortable(),
                TextColumn::make('quantity_accepted')
                    ->label('Qty Baik')
                    ->numeric(2)
                    ->sortable(),
                TextColumn::make('quantity_rejected')
                    ->label('Qty Tolak')
                    ->numeric(2)
                    ->sortable(),
                TextColumn::make('unit_price')
                    ->label('Harga Satuan')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('goods_receipt_id')
                    ->label('GRN')
                    ->relationship('goodsReceipt', 'grn_number'),
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