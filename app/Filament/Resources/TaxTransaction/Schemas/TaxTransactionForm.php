<?php

namespace App\Filament\Resources\TaxTransaction\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TaxTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Transaksi Pajak')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('tax_config_id')
                            ->label('Konfigurasi Pajak')
                            ->relationship('taxConfig', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('reference_type')
                            ->label('Tipe Referensi')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('reference_id')
                            ->label('ID Referensi')
                            ->required()
                            ->numeric(),
                        TextInput::make('base_amount')
                            ->label('Dasar Pengenaan Pajak')
                            ->numeric()
                            ->required(),
                        TextInput::make('tax_amount')
                            ->label('Jumlah Pajak')
                            ->numeric()
                            ->required(),
                        DatePicker::make('tax_date')
                            ->label('Tanggal Pajak')
                            ->required(),
                        Select::make('payment_status')
                            ->label('Status Pembayaran')
                            ->options([
                                'pending' => 'Menunggu',
                                'paid' => 'Dibayar',
                                'overdue' => 'Terlambat',
                            ])
                            ->required()
                            ->default('pending'),
                        DatePicker::make('paid_date')
                            ->label('Tanggal Dibayar')
                            ->nullable(),
                    ]),
            ]);
    }
}
