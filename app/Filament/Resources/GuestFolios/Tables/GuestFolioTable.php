<?php

namespace App\Filament\Resources\GuestFolios\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GuestFolioTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('folio_number')
                    ->label('Folio')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('booking.guest_name')
                    ->label('Tamu')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('booking.room.room_number')
                    ->label('Kamar')
                    ->sortable(),
                TextColumn::make('total_room_charges')
                    ->label('Biaya Kamar')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('total_service_charges')
                    ->label('Biaya Layanan')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('grand_total')
                    ->label('Grand Total')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('balance_due')
                    ->label('Sisa Tagihan')
                    ->money('IDR')
                    ->sortable(),
                BadgeColumn::make('payment_status')
                    ->label('Pembayaran')
                    ->colors([
                        'danger' => 'pending',
                        'warning' => 'partially_paid',
                        'success' => 'paid',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'partially_paid' => 'Dibayar Sebagian',
                        'paid' => 'Lunas',
                        default => $state,
                    }),
                TextColumn::make('settled_at')
                    ->label('Lunas Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('-'),
            ])
            ->defaultSort('created_at', 'desc')
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
