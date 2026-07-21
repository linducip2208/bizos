<?php

namespace App\Filament\Resources\DeliveryOrder\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class DeliveryOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pengiriman')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('invoice_id')
                            ->label('Invoice')
                            ->relationship('invoice', 'invoice_number')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('pos_transaction_id')
                            ->label('Transaksi POS')
                            ->relationship('posTransaction', 'receipt_number')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('customer_name')
                            ->label('Nama Penerima')
                            ->required()
                            ->maxLength(200),
                        Textarea::make('delivery_address')
                            ->label('Alamat Pengiriman')
                            ->required()
                            ->rows(2),
                        DatePicker::make('delivery_date')
                            ->label('Tanggal Kirim')
                            ->required()
                            ->default(now()),
                        Select::make('driver_id')
                            ->label('Driver')
                            ->relationship('driver', 'first_name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('vehicle_id')
                            ->label('Kendaraan')
                            ->relationship('vehicle', 'plate_number')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Menunggu',
                                'picked' => 'Diambil',
                                'in_transit' => 'Dalam Perjalanan',
                                'delivered' => 'Terkirim',
                                'failed' => 'Gagal',
                                'returned' => 'Dikembalikan',
                            ])
                            ->default('pending')
                            ->required(),
                        DateTimePicker::make('estimated_arrival')
                            ->label('Estimasi Tiba')
                            ->nullable(),
                        DateTimePicker::make('actual_arrival')
                            ->label('Waktu Tiba Aktual')
                            ->nullable(),
                        TextInput::make('receiver_name')
                            ->label('Nama Penerima Barang')
                            ->maxLength(200)
                            ->nullable(),
                        TextInput::make('gps_lat')
                            ->label('GPS Latitude')
                            ->numeric()
                            ->nullable(),
                        TextInput::make('gps_lng')
                            ->label('GPS Longitude')
                            ->numeric()
                            ->nullable(),
                    ]),
                Section::make('POD & Catatan')
                    ->columns(1)
                    ->schema([
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->nullable(),
                    ]),
            ]);
    }
}