<?php

namespace App\Filament\Resources\PosPayments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PosPaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pembayaran POS')
                    ->columns(2)
                    ->schema([
                        Select::make('transaction_id')
                            ->label('Transaksi')
                            ->relationship('transaction', 'receipt_number')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->required()
                            ->options([
                                'tunai' => 'Tunai',
                                'debit' => 'Kartu Debit',
                                'kredit' => 'Kartu Kredit',
                                'qris' => 'QRIS',
                                'transfer' => 'Transfer',
                                'ewallet' => 'E-Wallet',
                                'lainnya' => 'Lainnya',
                            ]),
                        TextInput::make('amount')
                            ->label('Jumlah')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                        TextInput::make('reference_number')
                            ->label('No. Referensi')
                            ->maxLength(100),
                        DateTimePicker::make('paid_at')
                            ->label('Dibayar Pada')
                            ->default(now()),
                    ]),
            ]);
    }
}
