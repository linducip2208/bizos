<?php

namespace App\Filament\Resources\ColdChainLogResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;

class ColdChainLogTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('deliveryOrder.do_number')
                    ->label('No. Surat Jalan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sensor_id')
                    ->label('Sensor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('temperature_celsius')
                    ->label('Suhu (°C)')
                    ->numeric()
                    ->sortable()
                    ->color(fn($state) => ($state < 0 || $state > 8) ? 'danger' : 'success'),
                TextColumn::make('humidity_percent')
                    ->label('Kelembaban (%)')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('recorded_at')
                    ->label('Waktu Rekam')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                IconColumn::make('is_breached')
                    ->label('Breach')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('success'),
                TextColumn::make('breach_details')
                    ->label('Detail Breach')
                    ->limit(50),
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
