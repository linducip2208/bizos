<?php

namespace App\Filament\Resources\DeliveryOrder\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DeliveryOrderTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('do_number')
                    ->label('No. Surat Jalan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->label('Penerima')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('delivery_address')
                    ->label('Alamat')
                    ->limit(40)
                    ->searchable(),
                TextColumn::make('delivery_date')
                    ->label('Tanggal Kirim')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('driver.first_name')
                    ->label('Driver')
                    ->sortable(),
                TextColumn::make('vehicle.plate_number')
                    ->label('Kendaraan')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn($state) => match($state) {
                        'pending' => 'Menunggu',
                        'picked' => 'Diambil',
                        'in_transit' => 'Dalam Perjalanan',
                        'delivered' => 'Terkirim',
                        'failed' => 'Gagal',
                        'returned' => 'Dikembalikan',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'pending' => 'gray',
                        'picked' => 'info',
                        'in_transit' => 'warning',
                        'delivered' => 'success',
                        'failed' => 'danger',
                        'returned' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('estimated_arrival')
                    ->label('Estimasi Tiba')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                TextColumn::make('actual_arrival')
                    ->label('Tiba Aktual')
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