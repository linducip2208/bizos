<?php

namespace App\Filament\Resources\PosTransactions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PosTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Transaksi POS')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('shift_id')
                            ->label('Shift Kasir')
                            ->relationship('shift', 'id')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('receipt_number')
                            ->label('No. Struk')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        Select::make('member_id')
                            ->label('Member')
                            ->relationship('member', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('cashier_id')
                            ->label('Kasir')
                            ->relationship('cashier', 'first_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        DateTimePicker::make('transaction_date')
                            ->label('Tanggal Transaksi')
                            ->required()
                            ->default(now()),
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        TextInput::make('discount_total')
                            ->label('Total Diskon')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        TextInput::make('tax_total')
                            ->label('Total Pajak')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        TextInput::make('grand_total')
                            ->label('Grand Total')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        Select::make('payment_status')
                            ->label('Status Pembayaran')
                            ->required()
                            ->default('belum_dibayar')
                            ->options([
                                'belum_dibayar' => 'Belum Dibayar',
                                'sebagian' => 'Sebagian',
                                'lunas' => 'Lunas',
                                'dibatalkan' => 'Dibatalkan',
                            ]),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
