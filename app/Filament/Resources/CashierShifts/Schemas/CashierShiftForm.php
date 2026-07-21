<?php

namespace App\Filament\Resources\CashierShifts\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CashierShiftForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Shift')
                    ->columns(3)
                    ->schema([
                        Select::make('employee_id')
                            ->label('Kasir')
                            ->relationship('employee', 'first_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('branch_id')
                            ->label('Cabang')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        DatePicker::make('shift_date')
                            ->label('Tanggal Shift')
                            ->required()
                            ->default(now()),
                        DateTimePicker::make('opening_time')
                            ->label('Waktu Buka')
                            ->required(),
                        TextInput::make('opening_balance')
                            ->label('Saldo Awal')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                    ]),
                Section::make('Penutupan Shift')
                    ->columns(3)
                    ->schema([
                        DateTimePicker::make('closing_time')
                            ->label('Waktu Tutup'),
                        TextInput::make('closing_balance')
                            ->label('Saldo Akhir')
                            ->numeric()
                            ->prefix('Rp'),
                        TextInput::make('expected_cash')
                            ->label('Kas Diharapkan')
                            ->numeric()
                            ->prefix('Rp'),
                        TextInput::make('actual_cash')
                            ->label('Kas Aktual')
                            ->numeric()
                            ->prefix('Rp'),
                        TextInput::make('difference')
                            ->label('Selisih')
                            ->numeric()
                            ->prefix('Rp'),
                        TextInput::make('total_transactions')
                            ->label('Total Transaksi')
                            ->numeric()
                            ->integer()
                            ->default(0),
                        TextInput::make('total_sales')
                            ->label('Total Penjualan')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'open' => 'Buka',
                                'closed' => 'Tutup',
                            ])
                            ->default('open')
                            ->required(),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
