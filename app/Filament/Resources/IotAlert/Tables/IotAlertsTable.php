<?php

namespace App\Filament\Resources\IotAlert\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class IotAlertsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('device.name')
                    ->label('Perangkat')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(50),
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'threshold_breach' => 'Ambang Batas',
                        'rate_of_change' => 'Perubahan Cepat',
                        'anomaly' => 'Anomali',
                        'battery_low' => 'Baterai Lemah',
                        'offline' => 'Offline',
                        'predictive_maintenance' => 'Prediksi Maintenance',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'threshold_breach' => 'warning',
                        'rate_of_change' => 'info',
                        'anomaly' => 'danger',
                        'battery_low' => 'warning',
                        'offline' => 'gray',
                        'predictive_maintenance' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('severity')
                    ->label('Severitas')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'info' => 'info',
                        'warning' => 'warning',
                        'critical' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'info' => 'Info',
                        'warning' => 'Warning',
                        'critical' => 'Kritis',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'danger',
                        'acknowledged' => 'warning',
                        'resolved' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'active' => 'Aktif',
                        'acknowledged' => 'Diakui',
                        'resolved' => 'Terselesaikan',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'threshold_breach' => 'Ambang Batas',
                        'rate_of_change' => 'Perubahan Cepat',
                        'anomaly' => 'Anomali',
                        'battery_low' => 'Baterai Lemah',
                        'offline' => 'Offline',
                        'predictive_maintenance' => 'Prediksi Maintenance',
                    ]),
                SelectFilter::make('severity')
                    ->label('Severitas')
                    ->options([
                        'info' => 'Info',
                        'warning' => 'Warning',
                        'critical' => 'Kritis',
                    ]),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktif',
                        'acknowledged' => 'Diakui',
                        'resolved' => 'Terselesaikan',
                    ]),
                SelectFilter::make('iot_device_id')
                    ->label('Perangkat')
                    ->relationship('device', 'name')
                    ->searchable()
                    ->preload(),
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