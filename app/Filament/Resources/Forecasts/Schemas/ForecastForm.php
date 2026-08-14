<?php

namespace App\Filament\Resources\Forecasts\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ForecastForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Forecast')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama Forecast')
                            ->required()
                            ->maxLength(255),
                        Select::make('forecast_type')
                            ->label('Tipe Forecast')
                            ->options([
                                'revenue' => 'Pendapatan',
                                'expense' => 'Pengeluaran',
                                'cash_flow' => 'Arus Kas',
                                'all' => 'Semua',
                            ])
                            ->required()
                            ->default('all'),
                        TextInput::make('fiscal_year')
                            ->label('Tahun Fiskal')
                            ->numeric()
                            ->required()
                            ->minValue(2000)
                            ->maxValue(2100),
                        DatePicker::make('period_start')
                            ->label('Periode Mulai')
                            ->required(),
                        DatePicker::make('period_end')
                            ->label('Periode Selesai')
                            ->required()
                            ->afterOrEqual('period_start'),
                        Select::make('frequency')
                            ->label('Frekuensi')
                            ->options([
                                'monthly' => 'Bulanan',
                                'quarterly' => 'Kuartalan',
                                'annual' => 'Tahunan',
                            ])
                            ->required()
                            ->default('monthly'),
                        TextInput::make('version')
                            ->label('Versi')
                            ->numeric()
                            ->required()
                            ->default(1)
                            ->minValue(1),
                        Toggle::make('is_rolling')
                            ->label('Rolling Forecast')
                            ->default(false),
                        Select::make('baseline_budget_id')
                            ->label('Anggaran Dasar')
                            ->relationship('baselineBudget', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('total_amount')
                            ->label('Total Jumlah')
                            ->numeric()
                            ->default(0),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Dipublikasi',
                                'archived' => 'Diarsipkan',
                            ])
                            ->required()
                            ->default('draft'),
                        Select::make('created_by')
                            ->label('Dibuat Oleh')
                            ->relationship('createdBy', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
            ]);
    }
}
