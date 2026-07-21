<?php

namespace App\Filament\Resources\IotDevice\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;

class IotDeviceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Perangkat')
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
                            ->label('Nama Perangkat')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('device_token')
                            ->label('Token Perangkat')
                            ->required()
                            ->maxLength(128)
                            ->unique(ignoreRecord: true),
                        Select::make('type')
                            ->label('Tipe')
                            ->options([
                                'sensor_temperature' => 'Sensor Suhu',
                                'sensor_vibration' => 'Sensor Getaran',
                                'energy_meter' => 'Meter Energi',
                                'rfid_reader' => 'Pembaca RFID',
                                'smart_scale' => 'Timbangan Pintar',
                            ])
                            ->required(),
                        TextInput::make('model')
                            ->label('Model')
                            ->maxLength(100)
                            ->nullable(),
                        TextInput::make('serial_number')
                            ->label('Nomor Seri')
                            ->maxLength(100)
                            ->nullable(),
                        TextInput::make('location')
                            ->label('Lokasi')
                            ->maxLength(255)
                            ->nullable(),
                    ]),
                Section::make('Status & Konfigurasi')
                    ->columns(3)
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'online' => 'Online',
                                'offline' => 'Offline',
                                'maintenance' => 'Maintenance',
                                'error' => 'Error',
                            ])
                            ->default('offline'),
                        TextInput::make('firmware_version')
                            ->label('Versi Firmware')
                            ->maxLength(50)
                            ->nullable(),
                        DatePicker::make('installed_at')
                            ->label('Tanggal Instalasi')
                            ->nullable(),
                        DatePicker::make('next_maintenance_at')
                            ->label('Maintenance Selanjutnya')
                            ->nullable(),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}