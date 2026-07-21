<?php

namespace App\Filament\Resources\RoomBookings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RoomBookingTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('room.room_number')
                    ->label('Kamar')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('guest_name')
                    ->label('Tamu')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('guest_phone')
                    ->label('Telepon')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('check_in_date')
                    ->label('Check-in')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('check_out_date')
                    ->label('Check-out')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('total_room_charge')
                    ->label('Biaya Kamar')
                    ->money('IDR')
                    ->sortable(),
                BadgeColumn::make('booking_source')
                    ->label('Sumber')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'direct' => 'Langsung',
                        'traveloka' => 'Traveloka',
                        'agoda' => 'Agoda',
                        'booking_com' => 'Booking.com',
                        'other' => 'Lainnya',
                        default => $state,
                    }),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray' => 'pending',
                        'info' => 'confirmed',
                        'success' => 'checked_in',
                        'primary' => 'checked_out',
                        'danger' => 'cancelled',
                        'warning' => 'no_show',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'confirmed' => 'Konfirmasi',
                        'checked_in' => 'Check-in',
                        'checked_out' => 'Check-out',
                        'cancelled' => 'Batal',
                        'no_show' => 'No Show',
                        default => $state,
                    }),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('check_in_date', 'desc')
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
