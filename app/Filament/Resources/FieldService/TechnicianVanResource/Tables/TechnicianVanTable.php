<?php

namespace App\Filament\Resources\FieldService\TechnicianVanResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;

class TechnicianVanTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('technician.first_name')
                    ->label('Teknisi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('license_plate')
                    ->label('Plat Nomor')
                    ->searchable(),
                TextColumn::make('vehicle.plate_number')
                    ->label('Kendaraan Perusahaan')
                    ->searchable(),
                TextColumn::make('current_location_lat')
                    ->label('Lat')
                    ->formatStateUsing(fn ($state) => $state ? round($state, 6) : '-'),
                TextColumn::make('current_location_lng')
                    ->label('Lng')
                    ->formatStateUsing(fn ($state) => $state ? round($state, 6) : '-'),
                TextColumn::make('last_location_update')
                    ->label('Update Terakhir')
                    ->dateTime('d M Y H:i'),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
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
