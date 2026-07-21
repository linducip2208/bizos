<?php

namespace App\Filament\Resources\PayrollPeriod\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PayrollPeriodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Periode Gaji')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('period_code')
                            ->label('Kode Periode')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->required(),
                        DatePicker::make('end_date')
                            ->label('Tanggal Selesai')
                            ->required()
                            ->afterOrEqual('start_date'),
                        DatePicker::make('payment_date')
                            ->label('Tanggal Pembayaran')
                            ->required()
                            ->afterOrEqual('end_date'),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'processing' => 'Diproses',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->required()
                            ->default('draft'),
                    ]),
                Section::make('Ringkasan')
                    ->columns(2)
                    ->schema([
                        TextInput::make('total_gross')
                            ->label('Total Kotor')
                            ->numeric()
                            ->default(0),
                        TextInput::make('total_deductions')
                            ->label('Total Potongan')
                            ->numeric()
                            ->default(0),
                        TextInput::make('total_net')
                            ->label('Total Bersih')
                            ->numeric()
                            ->default(0),
                        TextInput::make('total_employees')
                            ->label('Total Karyawan')
                            ->numeric()
                            ->default(0),
                    ]),
            ]);
    }
}
