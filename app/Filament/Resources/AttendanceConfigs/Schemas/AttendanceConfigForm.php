<?php

namespace App\Filament\Resources\AttendanceConfigs\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AttendanceConfigForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Konfigurasi Absensi')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('method')
                            ->label('Metode')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('gps_radius_meters')
                            ->label('Radius GPS (Meter)')
                            ->numeric()
                            ->nullable(),
                        TextInput::make('gps_latitude')
                            ->label('Latitude GPS')
                            ->numeric()
                            ->nullable(),
                        TextInput::make('gps_longitude')
                            ->label('Longitude GPS')
                            ->numeric()
                            ->nullable(),
                    ]),
                Section::make('Pengaturan Lanjutan')
                    ->columns(2)
                    ->schema([
                        Toggle::make('require_selfie')
                            ->label('Wajib Selfie')
                            ->default(false),
                        Toggle::make('require_wfh_photo')
                            ->label('Wajib Foto WFH')
                            ->default(false),
                        Toggle::make('auto_clock_out')
                            ->label('Auto Clock Out')
                            ->default(false),
                        DateTimePicker::make('auto_clock_out_time')
                            ->label('Jam Auto Clock Out')
                            ->nullable(),
                    ]),
            ]);
    }
}