<?php

namespace App\Filament\Resources\IotDevice\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IotDevicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Perangkat')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'sensor_temperature' => 'Sensor Suhu',
                        'sensor_vibration' => 'Sensor Getaran',
                        'energy_meter' => 'Meter Energi',
                        'rfid_reader' => 'RFID Reader',
                        'smart_scale' => 'Timbangan Pintar',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'sensor_temperature' => 'danger',
                        'sensor_vibration' => 'warning',
                        'energy_meter' => 'success',
                        'rfid_reader' => 'info',
                        'smart_scale' => 'primary',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('location')
                    ->label('Lokasi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'online' => 'success',
                        'offline' => 'danger',
                        'maintenance' => 'warning',
                        'error' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'online' => 'Online',
                        'offline' => 'Offline',
                        'maintenance' => 'Maintenance',
                        'error' => 'Error',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('battery_level')
                    ->label('Baterai')
                    ->suffix('%')
                    ->sortable()
                    ->color(fn($state) => $state < 20 ? 'danger' : ($state < 40 ? 'warning' : 'success')),
                TextColumn::make('last_seen_at')
                    ->label('Terakhir Terlihat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->defaultSort('name', 'asc')
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