<?php

namespace App\Filament\Resources\WifiAccessPoints\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WifiAccessPointForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi WiFi')
                    ->columns(2)
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
                        TextInput::make('ssid')
                            ->label('SSID')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('bssid')
                            ->label('BSSID')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('ip_address')
                            ->label('Alamat IP')
                            ->nullable()
                            ->maxLength(45),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}
