<?php

namespace App\Filament\Resources\EcommerceInventoryLogResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EcommerceInventoryLogTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('channel.channel_name')
                    ->label('Channel')
                    ->formatStateUsing(fn($state) => match($state) {
                        'shopee' => 'Shopee',
                        'tokopedia' => 'Tokopedia',
                        'bukalapak' => 'Bukalapak',
                        'tiktok_shop' => 'TikTok Shop',
                        'lazada' => 'Lazada',
                        'blibli' => 'Blibli',
                        default => $state,
                    })
                    ->badge()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('old_stock')
                    ->label('Stok Lama')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('new_stock')
                    ->label('Stok Baru')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('channel_stock')
                    ->label('Stok Channel')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sync_status')
                    ->label('Status')
                    ->formatStateUsing(fn($state) => match($state) {
                        'pending' => 'Menunggu',
                        'synced' => 'Tersinkron',
                        'failed' => 'Gagal',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'pending' => 'warning',
                        'synced' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('synced_at')
                    ->label('Waktu Sinkron')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->defaultSort('synced_at', 'desc')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
