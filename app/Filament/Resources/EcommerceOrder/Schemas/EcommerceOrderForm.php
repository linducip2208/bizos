<?php

namespace App\Filament\Resources\EcommerceOrder\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Schema;

class EcommerceOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pesanan E-Commerce')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('channel_id')
                            ->label('Channel')
                            ->relationship('channel', 'channel_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('channel_order_id')
                            ->label('ID Pesanan Channel')
                            ->required()
                            ->maxLength(100),
                        DateTimePicker::make('order_date')
                            ->label('Tanggal Pesan')
                            ->required()
                            ->default(now()),
                        TextInput::make('customer_name')
                            ->label('Nama Pelanggan')
                            ->required()
                            ->maxLength(200),
                        TextInput::make('customer_phone')
                            ->label('Telepon')
                            ->maxLength(30)
                            ->nullable(),
                        Textarea::make('customer_address')
                            ->label('Alamat')
                            ->rows(2)
                            ->nullable(),
                        TextInput::make('shipping_method')
                            ->label('Metode Kirim')
                            ->maxLength(50)
                            ->nullable(),
                        TextInput::make('shipping_cost')
                            ->label('Biaya Kirim')
                            ->numeric()
                            ->default(0),
                        TextInput::make('total_amount')
                            ->label('Total')
                            ->numeric()
                            ->required(),
                        Select::make('channel_status')
                            ->label('Status Channel')
                            ->options([
                                'unpaid' => 'Belum Bayar',
                                'paid' => 'Sudah Bayar',
                                'shipped' => 'Dikirim',
                                'delivered' => 'Terkirim',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->default('unpaid')
                            ->required(),
                        Select::make('sync_status')
                            ->label('Status Sinkron')
                            ->options([
                                'pending' => 'Menunggu',
                                'synced' => 'Tersinkron',
                                'failed' => 'Gagal',
                            ])
                            ->default('pending')
                            ->required(),
                        Select::make('pos_transaction_id')
                            ->label('Transaksi POS')
                            ->relationship('posTransaction', 'receipt_number')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ]),
            ]);
    }
}