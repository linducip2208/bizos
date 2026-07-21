<?php

namespace App\Filament\Resources\FleetGpsTrackResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;

class FleetGpsTrackTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('vehicle.plate_number')
                    ->label('Kendaraan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('driver.first_name')
                    ->label('Driver')
                    ->sortable(),
                TextColumn::make('latitude')
                    ->label('Lat')
                    ->numeric(6)
                    ->sortable(),
                TextColumn::make('longitude')
                    ->label('Lng')
                    ->numeric(6)
                    ->sortable(),
                TextColumn::make('speed_kmh')
                    ->label('Kecepatan')
                    ->numeric(1)
                    ->suffix(' km/h')
                    ->sortable(),
                TextColumn::make('heading')
                    ->label('Heading')
                    ->numeric(1)
                    ->suffix('°')
                    ->sortable(),
                IconColumn::make('ignition_status')
                    ->label('Mesin')
                    ->boolean(),
                TextColumn::make('recorded_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('recorded_at', 'desc')
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
