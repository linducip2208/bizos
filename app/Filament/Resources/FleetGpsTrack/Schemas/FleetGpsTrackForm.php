<?php

namespace App\Filament\Resources\FleetGpsTrack\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Schema;

class FleetGpsTrackForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data GPS')
                    ->columns(2)
                    ->schema([
                        Select::make('vehicle_id')
                            ->label('Kendaraan')
                            ->relationship('vehicle', 'plate_number')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('driver_id')
                            ->label('Driver')
                            ->relationship('driver', 'first_name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('latitude')
                            ->label('Latitude')
                            ->numeric()
                            ->step(0.0000001)
                            ->required(),
                        TextInput::make('longitude')
                            ->label('Longitude')
                            ->numeric()
                            ->step(0.0000001)
                            ->required(),
                        TextInput::make('speed_kmh')
                            ->label('Kecepatan (km/h)')
                            ->numeric()
                            ->step(0.1)
                            ->nullable(),
                        TextInput::make('heading')
                            ->label('Heading (°)')
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