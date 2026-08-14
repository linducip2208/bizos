<?php

namespace App\Filament\Resources\IntercompanyTransactions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IntercompanyTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Transaksi')
                    ->columns(3)
                    ->schema([
                        Select::make('from_company_id')
                            ->label('Dari Perusahaan')
                            ->relationship('fromCompany', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('to_company_id')
                            ->label('Ke Perusahaan')
                            ->relationship('toCompany', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('transaction_type')
                            ->label('Tipe Transaksi')
                            ->options([
                                'sale' => 'Penjualan',
                                'purchase' => 'Pembelian',
                                'transfer' => 'Transfer',
                                'payment' => 'Pembayaran',
                                'expense_allocation' => 'Alokasi Biaya',
                            ])
                            ->required(),
                        DatePicker::make('transaction_date')
                            ->label('Tanggal Transaksi')
                            ->default(now())
                            ->required(),
                        TextInput::make('reference_number')
                            ->label('Nomor Referensi')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('amount')
                            ->label('Jumlah')
                            ->numeric()
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
                            ->default(1),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'pending_approval' => 'Pending Approval',
                                'approved' => 'Approved',
                                'completed' => 'Completed',
                                'rejected' => 'Rejected',
                                'void' => 'Void',
                            ])
                            ->disabled()
                            ->dehydrated(false)
                            ->default('draft'),
                    ]),
                Section::make('Catatan')
                    ->columns(1)
                    ->schema([
                        KeyValue::make('notes')
                            ->label('Catatan Tambahan')
                            ->nullable(),
                    ]),
            ]);
    }
}
