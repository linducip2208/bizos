<?php

namespace App\Filament\Resources\ColdChainLog\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Schema;

class ColdChainLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Cold Chain')
                    ->columns(2)
                    ->schema([
                        Select::make('delivery_order_id')
                            ->label('Surat Jalan')
                            ->relationship('deliveryOrder', 'do_number')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('sensor_id')
                            ->label('ID Sensor')
                            ->maxLength(100)
                            ->nullable(),
                        TextInput::make('temperature_celsius')
                            ->label('Suhu (°C)')
                            ->numeric()
                            ->step(0.1)
                            ->nullable(),
                        TextInput::make('humidity_percent')
                            ->label('Kelembaban (%)')
                            ->numeric()
                            ->step(0.1)
                            ->nullable(),
                        DateTimePicker::make('recorded_at')
                            ->label('Waktu Rekam')
                            ->required()
                            ->default(now()),
                    ]),
            ]);
    }
}