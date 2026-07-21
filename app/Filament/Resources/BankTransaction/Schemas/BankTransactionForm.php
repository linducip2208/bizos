<?php

namespace App\Filament\Resources\BankTransaction\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BankTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Transaksi')
                    ->columns(3)
                    ->schema([
                        Select::make('bank_account_id')
                            ->label('Rekening Bank')
                            ->relationship('bankAccount', 'account_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        DatePicker::make('transaction_date')
                            ->label('Tanggal Transaksi')
                            ->required(),
                        Select::make('transaction_type')
                            ->label('Tipe Transaksi')
                            ->options([
                                'credit' => 'Kredit (Masuk)',
                                'debit' => 'Debit (Keluar)',
                            ])
                            ->required(),
                        TextInput::make('amount')
                            ->label('Jumlah')
                            ->numeric()
                            ->required()
                            ->inputMode('decimal'),
                        TextInput::make('reference_number')
                            ->label('Nomor Referensi')
                            ->nullable()
                            ->maxLength(100),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Toggle::make('is_reconciled')
                            ->label('Sudah Direkonsiliasi')
                            ->default(false),
                    ]),
            ]);
    }
}