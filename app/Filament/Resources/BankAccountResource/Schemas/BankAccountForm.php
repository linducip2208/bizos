<?php

namespace App\Filament\Resources\BankAccountResource\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BankAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Rekening')
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
                        TextInput::make('bank_name')
                            ->label('Nama Bank')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('account_number')
                            ->label('Nomor Rekening')
                            ->required()
                            ->maxLength(50),
                        TextInput::make('account_name')
                            ->label('Nama Pemilik Rekening')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('branch')
                            ->label('Cabang')
                            ->nullable()
                            ->maxLength(100),
                    ]),
                Section::make('Saldo')
                    ->columns(3)
                    ->schema([
                        TextInput::make('opening_balance')
                            ->label('Saldo Awal')
                            ->numeric()
                            ->default(0)
                            ->inputMode('decimal'),
                        TextInput::make('current_balance')
                            ->label('Saldo Saat Ini')
                            ->numeric()
                            ->default(0)
                            ->inputMode('decimal'),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}
