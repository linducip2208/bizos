<?php

namespace App\Filament\Resources\EnergyMeter\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EnergyMeterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Meter')
                    ->columns(3)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('branch_id')
                            ->label('Cabang')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('name')
                            ->label('Nama Meter')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('meter_number')
                            ->label('Nomor Meter')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true),
                        TextInput::make('location')
                            ->label('Lokasi')
                            ->maxLength(255)
                            ->nullable(),
                        TextInput::make('utility_provider')
                            ->label('Provider Listrik')
                            ->maxLength(100)
                            ->nullable()
                            ->placeholder('Contoh: PLN'),
                    ]),
                Section::make('Tarif & Status')
                    ->columns(3)
                    ->schema([
                        TextInput::make('rate_per_kwh')
                            ->label('Tarif per kWh (Rp)')
                            ->numeric()
                            ->default(1500)
                            ->inputMode('decimal'),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Aktif',
                                'inactive' => 'Nonaktif',
                                'maintenance' => 'Maintenance',
                            ])
                            ->default('active'),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}