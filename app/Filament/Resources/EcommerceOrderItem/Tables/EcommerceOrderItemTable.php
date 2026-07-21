<?php

namespace App\Filament\Resources\EcommerceOrderItem\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EcommerceOrderItemTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ecommerceOrder.channel_order_id')
                    ->label('ID Pesanan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('channel_sku')
                    ->label('SKU Channel')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Produk BizOS')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product_name')
                    ->label('Nama Produk')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label('Qty')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('unit_price')
                    ->label('Harga')
                    ->numeric('Rp')
                    ->sortable()
                    ->money('IDR'),
                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->numeric('Rp')
                    ->sortable()
                    ->money('IDR'),
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