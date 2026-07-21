<?php

namespace App\Filament\Resources\RoomBookings\Schemas;

use App\Models\Client;
use App\Models\Room;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RoomBookingForm
{
    public static function configure(Schema $schema): Schema
    {
        $companyId = auth()->user()->company_id;

        return $schema
            ->components([
                Section::make('Informasi Tamu')
                    ->schema([
                        Select::make('room_id')
                            ->label('Kamar')
                            ->options(Room::where('company_id', $companyId)->pluck('room_number', 'id'))
                            ->searchable()
                            ->required(),
                        Select::make('client_id')
                            ->label('Klien (Member)')
                            ->options(Client::where('company_id', $companyId)->pluck('name', 'id'))
                            ->searchable()
                            ->nullable(),
                        TextInput::make('guest_name')
                            ->label('Nama Tamu')
                            ->required()
                            ->maxLength(200),
                        TextInput::make('guest_phone')
                            ->label('Telepon')
                            ->maxLength(30),
                        TextInput::make('guest_email')
                            ->label('Email')
                            ->email()
                            ->maxLength(200),
                    ])->columns(2),

                Section::make('Detail Booking')
                    ->schema([
                        DatePicker::make('check_in_date')
                            ->label('Check-in')
                            ->required(),
                        DatePicker::make('check_out_date')
                            ->label('Check-out')
                            ->required()
                            ->afterOrEqual('check_in_date'),
                        TextInput::make('adults')
                            ->label('Dewasa')
                            ->integer()
                            ->default(1),
                        TextInput::make('children')
                            ->label('Anak-anak')
                            ->integer()
                            ->default(0),
                    ])->columns(2),

                Section::make('Sumber Booking')
                    ->schema([
                        Select::make('booking_source')
                            ->label('Sumber')
                            ->options([
                                'direct' => 'Langsung',
                                'traveloka' => 'Traveloka',
                                'agoda' => 'Agoda',
                                'booking_com' => 'Booking.com',
                                'other' => 'Lainnya',
                            ])
                            ->default('direct'),
                        TextInput::make('ota_booking_id')
                            ->label('OTA Booking ID')
                            ->maxLength(100)
                            ->nullable(),
                        TextInput::make('ota_commission_percent')
                            ->label('Komisi OTA (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->nullable(),
                        TextInput::make('total_room_charge')
                            ->label('Total Biaya Kamar (Rp)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp'),
                    ])->columns(2),

                Section::make('Status & Catatan')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'confirmed' => 'Dikonfirmasi',
                                'checked_in' => 'Check-in',
                                'checked_out' => 'Check-out',
                                'cancelled' => 'Dibatalkan',
                                'no_show' => 'No Show',
                            ])
                            ->default('pending')
                            ->required(),
                        Textarea::make('special_requests')
                            ->label('Permintaan Khusus')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
