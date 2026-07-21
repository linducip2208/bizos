<?php

namespace App\Filament\Resources\InvestmentResource\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;

class InvestmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Investasi')
                    ->columns(3)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('currency_id')
                            ->label('Mata Uang')
                            ->relationship('currency', 'code')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('bank_account_id')
                            ->label('Rekening Bank')
                            ->relationship('bankAccount', 'account_name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('name')
                            ->label('Nama Investasi')
                            ->required()
                            ->maxLength(255),
                        Select::make('type')
                            ->label('Tipe')
                            ->options([
                                'deposit' => 'Deposito',
                                'bond' => 'Obligasi',
                                'mutual_fund' => 'Reksadana',
                                'stock' => 'Saham',
                                'government_bond' => 'SBN',
                                'corporate_bond' => 'Obligasi Korporasi',
                                'money_market' => 'Pasar Uang',
                                'other' => 'Lainnya',
                            ])
                            ->required(),
                        TextInput::make('institution')
                            ->label('Institusi')
                            ->maxLength(255)
                            ->nullable(),
                        TextInput::make('investment_number')
                            ->label('Nomor Investasi')
                            ->maxLength(100)
                            ->nullable(),
                    ]),
                Section::make('Detail Keuangan')
                    ->columns(3)
                    ->schema([
                        TextInput::make('principal_amount')
                            ->label('Nilai Pokok')
                            ->numeric()
                            ->required()
                            ->inputMode('decimal'),
                        TextInput::make('current_value')
                            ->label('Nilai Saat Ini')
                            ->numeric()
                            ->nullable()
                            ->inputMode('decimal'),
                        TextInput::make('interest_rate_percent')
                            ->label('Bunga (%)')
                            ->numeric()
                            ->default(0)
                            ->inputMode('decimal')
                            ->suffix('% p.a.'),
                        Select::make('interest_type')
                            ->label('Tipe Bunga')
                            ->options([
                                'fixed' => 'Fixed',
                                'floating' => 'Floating',
                                'zero_coupon' => 'Zero Coupon',
                                'dividend' => 'Dividen',
                            ])
                            ->default('fixed'),
                        TextInput::make('interest_payment_frequency')
                            ->label('Frekuensi Pembayaran')
                            ->maxLength(50)
                            ->nullable()
                            ->placeholder('Contoh: Bulanan, Triwulan'),
                    ]),
                Section::make('Periode')
                    ->columns(3)
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->required(),
                        DatePicker::make('maturity_date')
                            ->label('Tanggal Jatuh Tempo')
                            ->nullable(),
                        DatePicker::make('next_interest_date')
                            ->label('Tanggal Bunga Berikutnya')
                            ->nullable(),
                    ]),
                Section::make('Status')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Aktif',
                                'matured' => 'Jatuh Tempo',
                                'liquidated' => 'Dilikuidasi',
                                'impaired' => 'Menurun',
                            ])
                            ->default('active'),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->columnSpanFull()
                            ->nullable(),
                    ]),
            ]);
    }
}
