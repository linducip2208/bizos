<?php

namespace App\Filament\Resources\IotReading\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class IotReadingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('device.name')
                    ->label('Perangkat')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('temperature_celsius')
                    ->label('Suhu (C)')
                    ->numeric(2, '.', '')
                    ->sortable()
                    ->color(fn($state) => $state > 35 ? 'danger' : ($state > 30 ? 'warning' : 'success')),
                TextColumn::make('humidity_percent')
                    ->label('Kelembaban (%)')
                    ->numeric(2, '.', '')
                    ->sortable(),
                TextColumn::make('vibration_mm_s')
                    ->label('Getaran (mm/s)')
                    ->numeric(4, '.', '')
                    ->sortable(),
                TextColumn::make('battery_level')
                    ->label('Baterai (%)')
                    ->numeric(2, '.', '')
                    ->sortable()
                    ->color(fn($state) => $state < 20 ? 'danger' : ($state < 40 ? 'warning' : 'success')),
                TextColumn::make('signal_strength_dbm')
                    ->label('Sinyal (dBm)')
                    ->numeric(2, '.', '')
                    ->sortable(),
                TextColumn::make('recorded_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('recorded_at', 'desc')
            ->filters([
                SelectFilter::make('iot_device_id')
                    ->label('Perangkat')
                    ->relationship('device', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('recorded_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('recorded_from')->label('Dari'),
                        \Filament\Forms\Components\DatePicker::make('recorded_to')->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['recorded_from'], fn($q, $d) => $q->whereDate('recorded_at', '>=', $d))
                            ->when($data['recorded_to'], fn($q, $d) => $q->whereDate('recorded_at', '<=', $d));
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}