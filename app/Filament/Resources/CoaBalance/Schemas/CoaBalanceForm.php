<?php

namespace App\Filament\Resources\CoaBalance\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CoaBalanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Saldo COA')
                    ->columns(2)
                    ->schema([
                        Select::make('coa_id')
                            ->label('Akun')
                            ->relationship('coa', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('year')
                            ->label('Tahun')
                            ->numeric()
                            ->required()
                            ->minValue(2000)
                            ->maxValue(2100),
                        TextInput::make('month')
                            ->label('Bulan')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(12),
                        TextInput::make('opening_balance')
                            ->label('Saldo Awal')
                            ->numeric()
                            ->default(0),
                        TextInput::make('debit_total')
                            ->label('Total Debit')
                            ->numeric()
                            ->default(0),
                        TextInput::make('credit_total')
                            ->label('Total Kredit')
                            ->numeric()
                            ->default(0),
                        TextInput::make('closing_balance')
                            ->label('Saldo Akhir')
                            ->numeric()
                            ->default(0),
                    ]),
            ]);
    }
}