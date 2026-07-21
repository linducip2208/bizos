<?php

namespace App\Filament\Resources\EcommerceChannel\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;

class EcommerceChannelTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('channel_name')
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
                    ->searchable()
                    ->sortable(),
                TextColumn::make('shop_id')
                    ->label('Shop ID')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('last_sync_at')
                    ->label('Sinkron Terakhir')
                    ->dateTime('d M Y, H:i')
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