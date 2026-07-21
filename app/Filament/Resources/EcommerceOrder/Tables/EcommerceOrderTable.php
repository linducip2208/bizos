<?php

namespace App\Filament\Resources\EcommerceOrder\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EcommerceOrderTable
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
                TextColumn::make('channel_order_id')
                    ->label('ID Pesanan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order_date')
                    ->label('Tanggal')
                    ->date('d M Y, H:i')
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->label('Pelanggan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->numeric('Rp')
                    ->sortable()
                    ->money('IDR'),
                TextColumn::make('channel_status')
                    ->label('Status Channel')
                    ->formatStateUsing(fn($state) => match($state) {
                        'unpaid' => 'Belum Bayar',
                        'paid' => 'Sudah Bayar',
                        'shipped' => 'Dikirim',
                        'delivered' => 'Terkirim',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'unpaid' => 'warning',
                        'paid' => 'info',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('sync_status')
                    ->label('Sinkron')
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
                TextColumn::make('shipping_method')
                    ->label('Kurir')
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