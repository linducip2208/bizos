<?php

namespace App\Filament\Resources\PropertyUnits\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PropertyUnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Unit')
                    ->schema([
                        Select::make('property_type')
                            ->label('Tipe Properti')
                            ->options([
                                'apartment' => 'Apartemen',
                                'house' => 'Rumah',
                                'shop' => 'Ruko',
                                'office' => 'Kantor',
                                'warehouse' => 'Gudang',
                            ])
                            ->default('house')
                            ->required(),
                        TextInput::make('unit_number')
                            ->label('Nomor Unit')
                            ->required()
                            ->maxLength(50),
                        TextInput::make('building_name')
                            ->label('Nama Gedung/Komplek')
                            ->maxLength(200),
                        TextInput::make('floor')
                            ->label('Lantai')
                            ->integer()
                            ->nullable(),
                    ])->columns(2),

                Section::make('Spesifikasi')
                    ->schema([
                        TextInput::make('land_area_sqm')
                            ->label('Luas Tanah (m2)')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('m2'),
                        TextInput::make('building_area_sqm')
                            ->label('Luas Bangunan (m2)')
                            ->numeric()
                            ->step(0.01)
                            ->suffix('m2'),
                        TextInput::make('bedrooms')
                            ->label('Kamar Tidur')
                            ->integer()
                            ->default(0),
                        TextInput::make('bathrooms')
                            ->label('Kamar Mandi')
                            ->integer()
                            ->default(0),
                    ])->columns(2),

                Section::make('Legal & Finansial')
                    ->schema([
                        Textarea::make('address')
                            ->label('Alamat')
                            ->maxLength(500)
                            ->columnSpanFull(),
                        TextInput::make('ownership_certificate')
                            ->label('Sertifikat (SHM/HGB)')
                            ->maxLength(100),
                        DatePicker::make('purchase_date')
                            ->label('Tanggal Beli'),
                        TextInput::make('purchase_price')
                            ->label('Harga Beli (Rp)')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp'),
                        TextInput::make('current_market_value')
                            ->label('Nilai Pasar Saat Ini (Rp)')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp'),
                    ])->columns(2),

                Section::make('Status')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'available' => 'Tersedia',
                                'rented' => 'Disewakan',
                                'sold' => 'Terjual',
                                'maintenance' => 'Perbaikan',
                                'vacant' => 'Kosong',
                            ])
                            ->default('available')
                            ->required(),
                    ]),
            ]);
    }
}