<?php

namespace App\Filament\Resources\Rooms\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RoomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kamar')
                    ->schema([
                        TextInput::make('room_number')
                            ->label('Nomor Kamar')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true),
                        Select::make('room_type')
                            ->label('Tipe Kamar')
                            ->options([
                                'standard' => 'Standard',
                                'deluxe' => 'Deluxe',
                                'suite' => 'Suite',
                                'family' => 'Family',
                                'presidential' => 'Presidential',
                            ])
                            ->default('standard')
                            ->required(),
                        TextInput::make('floor')
                            ->label('Lantai')
                            ->integer()
                            ->default(1),
                        Select::make('bed_type')
                            ->label('Tipe Kasur')
                            ->options([
                                'single' => 'Single',
                                'double' => 'Double',
                                'twin' => 'Twin',
                                'king' => 'King',
                            ])
                            ->default('double'),
                        TextInput::make('max_guests')
                            ->label('Maks. Tamu')
                            ->integer()
                            ->default(2),
                    ])->columns(3),

                Section::make('Harga')
                    ->schema([
                        TextInput::make('base_price')
                            ->label('Harga Dasar (Rp)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp'),
                        TextInput::make('weekend_price')
                            ->label('Harga Weekend (Rp)')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp'),
                        TextInput::make('holiday_price')
                            ->label('Harga Liburan (Rp)')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp'),
                    ])->columns(3),

                Section::make('Fasilitas')
                    ->schema([
                        CheckboxList::make('amenities')
                            ->label('Fasilitas')
                            ->options([
                                'wifi' => 'WiFi',
                                'tv' => 'TV',
                                'ac' => 'AC',
                                'breakfast' => 'Sarapan',
                                'balcony' => 'Balkon',
                                'bathtub' => 'Bathtub',
                            ])
                            ->columns(3),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),

                Section::make('Status')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'available' => 'Tersedia',
                                'occupied' => 'Terisi',
                                'dirty' => 'Kotor',
                                'maintenance' => 'Perbaikan',
                                'reserved' => 'Dipesan',
                            ])
                            ->default('available')
                            ->required(),
                        TextInput::make('current_guest_name')
                            ->label('Nama Tamu Saat Ini')
                            ->maxLength(200),
                    ])->columns(2),
            ]);
    }
}