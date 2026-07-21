<?php

namespace App\Filament\Resources\Rooms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RoomTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('room_number')
                    ->label('No. Kamar')
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('room_type')
                    ->label('Tipe')
                    ->colors([
                        'gray' => 'standard',
                        'info' => 'deluxe',
                        'success' => 'suite',
                        'warning' => 'family',
                        'primary' => 'presidential',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'standard' => 'Standard',
                        'deluxe' => 'Deluxe',
                        'suite' => 'Suite',
                        'family' => 'Family',
                        'presidential' => 'Presidential',
                        default => $state,
                    }),
                TextColumn::make('floor')
                    ->label('Lantai')
                    ->sortable(),
                TextColumn::make('bed_type')
                    ->label('Kasur')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'single' => 'Single',
                        'double' => 'Double',
                        'twin' => 'Twin',
                        'king' => 'King',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('max_guests')
                    ->label('Maks Tamu')
                    ->sortable(),
                TextColumn::make('base_price')
                    ->label('Harga Dasar')
                    ->money('IDR')
                    ->sortable(),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'available',
                        'danger' => 'occupied',
                        'warning' => 'dirty',
                        'gray' => 'maintenance',
                        'info' => 'reserved',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'available' => 'Tersedia',
                        'occupied' => 'Terisi',
                        'dirty' => 'Kotor',
                        'maintenance' => 'Perbaikan',
                        'reserved' => 'Dipesan',
                        default => $state,
                    }),
                TextColumn::make('current_guest_name')
                    ->label('Tamu')
                    ->searchable()
                    ->placeholder('-'),
            ])
            ->defaultSort('room_number', 'asc')
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