<?php

namespace App\Filament\Resources\BankFacilityResource\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;

class BankFacilityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Fasilitas')
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
                        TextInput::make('bank_name')
                            ->label('Nama Bank')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('name')
                            ->label('Nama Fasilitas')
                            ->required()
                            ->maxLength(255),
                        Select::make('facility_type')
                            ->label('Tipe Fasilitas')
                            ->options([
                                'overdraft' => 'Overdraft',
                                'term_loan' => 'Term Loan',
                                'revolving_credit' => 'Revolving Credit',
                                'lc' => 'L/C',
                                'bank_guarantee' => 'Bank Garansi',
                                'factoring' => 'Factoring',
                                'supply_chain_finance' => 'Supply Chain Finance',
                                'other' => 'Lainnya',
                            ])
                            ->required(),
                        TextInput::make('facility_number')
                            ->label('Nomor Fasilitas')
                            ->maxLength(100)
                            ->nullable(),
                    ]),
                Section::make('Limit & Bunga')
                    ->columns(3)
                    ->schema([
                        TextInput::make('limit_amount')
                            ->label('Limit')
                            ->numeric()
                            ->required()
                            ->inputMode('decimal'),
                        TextInput::make('utilized_amount')
                            ->label('Terpakai')
                            ->numeric()
                            ->default(0)
                            ->inputMode('decimal'),
                        TextInput::make('interest_rate_percent')
                            ->label('Bunga (%)')
                            ->numeric()
                            ->default(0)
                            ->inputMode('decimal')
                            ->suffix('% p.a.'),
                        TextInput::make('commitment_fee_percent')
                            ->label('Commitment Fee (%)')
                            ->numeric()
                            ->nullable()
                            ->inputMode('decimal')
                            ->suffix('%'),
                    ]),
                Section::make('Periode')
                    ->columns(3)
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->required(),
                        DatePicker::make('expiry_date')
                            ->label('Tanggal Berakhir')
                            ->required(),
                        DatePicker::make('review_date')
                            ->label('Tanggal Review')
                            ->nullable(),
                    ]),
                Section::make('Jaminan & Status')
                    ->columns(3)
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Aktif',
                                'expired' => 'Kadaluarsa',
                                'cancelled' => 'Dibatalkan',
                                'suspended' => 'Ditangguhkan',
                            ])
                            ->default('active'),
                        Toggle::make('is_secured')
                            ->label('Dengan Jaminan'),
                        TextInput::make('collateral_value')
                            ->label('Nilai Jaminan')
                            ->numeric()
                            ->nullable()
                            ->inputMode('decimal'),
                        Textarea::make('collateral_description')
                            ->label('Deskripsi Jaminan')
                            ->columnSpanFull()
                            ->nullable(),
                    ]),
                Section::make('Catatan')
                    ->columns(1)
                    ->schema([
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->nullable(),
                    ]),
            ]);
    }
}
