<?php

namespace App\Filament\Resources\BankReconciliation\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class BankReconciliationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Rekonsiliasi')
                    ->columns(3)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('bank_account_id')
                            ->label('Rekening Bank')
                            ->relationship('bankAccount', 'account_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'in_progress' => 'Dalam Proses',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->default('draft')
                            ->required(),
                        DatePicker::make('period_start')
                            ->label('Periode Mulai')
                            ->required(),
                        DatePicker::make('period_end')
                            ->label('Periode Akhir')
                            ->required(),
                        TextInput::make('opening_balance')
                            ->label('Saldo Awal')
                            ->numeric()
                            ->default(0)
                            ->inputMode('decimal'),
                        TextInput::make('closing_balance')
                            ->label('Saldo Akhir (Sistem)')
                            ->numeric()
                            ->default(0)
                            ->inputMode('decimal'),
                        TextInput::make('statement_balance')
                            ->label('Saldo Rekening Koran')
                            ->numeric()
                            ->default(0)
                            ->inputMode('decimal'),
                        TextInput::make('difference')
                            ->label('Selisih')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->inputMode('decimal'),
                    ]),
                Section::make('Catatan')
                    ->columns(1)
                    ->schema([
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->nullable()
                            ->rows(4),
                    ]),
            ]);
    }
}