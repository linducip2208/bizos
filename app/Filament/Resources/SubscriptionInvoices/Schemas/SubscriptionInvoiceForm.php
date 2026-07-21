<?php

namespace App\Filament\Resources\SubscriptionInvoices\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SubscriptionInvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Invoice')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('subscription_id')
                            ->label('Langganan')
                            ->relationship('subscription', 'id', fn ($query) => $query->with('company', 'plan'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('invoice_number')
                            ->label('Nomor Invoice')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Menunggu Pembayaran',
                                'paid' => 'Lunas',
                                'overdue' => 'Jatuh Tempo',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->required()
                            ->default('pending'),
                        TextInput::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->maxLength(255),
                    ]),
                Section::make('Nominal')
                    ->columns(3)
                    ->schema([
                        TextInput::make('amount')
                            ->label('Jumlah (Rp)')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                        TextInput::make('tax_amount')
                            ->label('Pajak (Rp)')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        TextInput::make('total')
                            ->label('Total (Rp)')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                    ]),
                Section::make('Periode')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('period_start')
                            ->label('Periode Mulai')
                            ->required(),
                        DatePicker::make('period_end')
                            ->label('Periode Akhir')
                            ->required(),
                        DatePicker::make('due_date')
                            ->label('Jatuh Tempo')
                            ->required(),
                        DateTimePicker::make('paid_at')
                            ->label('Tanggal Bayar')
                            ->nullable(),
                    ]),
            ]);
    }
}