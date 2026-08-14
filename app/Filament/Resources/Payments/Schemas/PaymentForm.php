<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Pembayaran')
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
                        TextInput::make('payment_number')
                            ->label('Nomor Pembayaran')
                            ->required()
                            ->maxLength(100),
                        DatePicker::make('payment_date')
                            ->label('Tanggal Pembayaran')
                            ->required(),
                        Select::make('currency_id')
                            ->label('Mata Uang')
                            ->relationship('currency', 'code')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('exchange_rate')
                            ->label('Kurs')
                            ->numeric()
                            ->minValue(0)
                            ->nullable(),
                        Select::make('payment_method_id')
                            ->label('Metode Pembayaran')
                            ->relationship('paymentMethod', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('amount')
                            ->label('Jumlah')
                            ->numeric()
                            ->required()
                            ->prefix('Rp'),
                        TextInput::make('reference_number')
                            ->label('Nomor Referensi')
                            ->maxLength(100)
                            ->nullable(),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'confirmed' => 'Dikonfirmasi',
                                'rejected' => 'Ditolak',
                                'void' => 'Void',
                            ])
                            ->default('pending')
                            ->required(),
                    ]),
            ]);
    }
}