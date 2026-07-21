<?php

namespace App\Filament\Resources\Vehicle\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class VehicleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kendaraan')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('plate_number')
                            ->label('Nomor Plat')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20),
                        TextInput::make('brand')
                            ->label('Merek')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('model')
                            ->label('Model')
                            ->maxLength(100),
                        TextInput::make('year')
                            ->label('Tahun')
                            ->numeric()
                            ->minValue(1990)
                            ->maxValue(date('Y') + 1),
                        Select::make('vehicle_type')
                            ->label('Tipe Kendaraan')
                            ->options([
                                'car' => 'Mobil',
                                'motorcycle' => 'Motor',
                                'truck' => 'Truk',
                            ])
                            ->default('car')
                            ->required(),
                        Select::make('fuel_type')
                            ->label('Jenis BBM')
                            ->options([
                                'gasoline' => 'Bensin',
                                'diesel' => 'Solar',
                                'electric' => 'Listrik',
                                'hybrid' => 'Hybrid',
                            ])
                            ->default('gasoline')
                            ->required(),
                        Select::make('ownership')
                            ->label('Kepemilikan')
                            ->options([
                                'company' => 'Perusahaan',
                                'leased' => 'Sewa',
                            ])
                            ->default('company')
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'available' => 'Tersedia',
                                'in_use' => 'Digunakan',
                                'maintenance' => 'Perawatan',
                                'sold' => 'Terjual',
                            ])
                            ->default('available')
                            ->required(),
                        TextInput::make('last_odometer')
                            ->label('Odometer Terakhir')
                            ->numeric()
                            ->default(0),
                        TextInput::make('color')
                            ->label('Warna')
                            ->maxLength(50),
                        TextInput::make('chassis_number')
                            ->label('Nomor Rangka')
                            ->maxLength(100),
                        TextInput::make('engine_number')
                            ->label('Nomor Mesin')
                            ->maxLength(100),
                        DatePicker::make('registration_expiry')
                            ->label('STNK Berlaku Sampai'),
                        DatePicker::make('insurance_expiry')
                            ->label('Asuransi Berlaku Sampai'),
                    ]),
                Section::make('Catatan')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3),
                    ]),
            ]);
    }
}