<?php

namespace App\Filament\Resources\SubscriptionPayments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SubscriptionPaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pembayaran')
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
                            ->required(),
                        TextInput::make('amount')
                            ->label('Jumlah (Rp)')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                        TextInput::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->maxLength(255),
                        TextInput::make('transaction_id')
                            ->label('ID Transaksi')
                            ->maxLength(255),
                        DateTimePicker::make('payment_date')
                            ->label('Tanggal Bayar'),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Menunggu Konfirmasi',
                                'confirmed' => 'Dikonfirmasi',
                                'rejected' => 'Ditolak',
                            ])
                            ->required()
                            ->default('pending'),
                    ]),
                Section::make('Bukti Pembayaran')
                    ->schema([
                        FileUpload::make('proof_path')
                            ->label('Bukti Transfer')
                            ->image()
                            ->directory('subscription-payments')
                            ->imagePreviewHeight('200')
                            ->maxSize(5120),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}