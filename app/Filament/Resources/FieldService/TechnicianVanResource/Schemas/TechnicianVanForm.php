<?php

namespace App\Filament\Resources\FieldService\TechnicianVanResource\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TechnicianVanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Van Teknisi')
                    ->columns(2)
                    ->schema([
                        Select::make('technician_id')
                            ->label('Teknisi')
                            ->relationship('technician', 'first_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('vehicle_id')
                            ->label('Kendaraan (Perusahaan)')
                            ->relationship('vehicle', 'plate_number')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('license_plate')
                            ->label('Plat Nomor')
                            ->maxLength(20),
                        TextInput::make('current_location_lat')
                            ->label('Latitude Saat Ini')
                            ->numeric()
                            ->disabled(),
                        TextInput::make('current_location_lng')
                            ->label('Longitude Saat Ini')
                            ->numeric()
                            ->disabled(),
                        TextInput::make('last_location_update')
                            ->label('Update Lokasi Terakhir')
                            ->disabled(),
                        Select::make('is_active')
                            ->label('Status')
                            ->required()
                            ->default(true)
                            ->options([
                                true => 'Aktif',
                                false => 'Nonaktif',
                            ]),
                    ]),
            ]);
    }
}
