<?php

namespace App\Filament\Resources\BankTransferResource\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class BankTransferForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Transfer')
                    ->columns(3)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('from_account_id')
                            ->label('Dari Rekening')
                            ->relationship('fromAccount', 'account_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('to_account_id')
                            ->label('Ke Rekening')
                            ->relationship('toAccount', 'account_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        DatePicker::make('transfer_date')
                            ->label('Tanggal Transfer')
                            ->required(),
                        TextInput::make('amount')
                            ->label('Jumlah')
                            ->numeric()
                            ->required()
                            ->inputMode('decimal'),
                        TextInput::make('exchange_rate')
                            ->label('Nilai Tukar')
                            ->numeric()
                            ->nullable()
                            ->helperText('Isi jika transfer antar mata uang berbeda')
                            ->inputMode('decimal'),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'completed' => 'Selesai',
                            ])
                            ->default('draft')
                            ->required(),
                    ]),
                Section::make('Detail')
                    ->columns(1)
                    ->schema([
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->nullable()
                            ->rows(3),
                    ]),
            ]);
    }
}
