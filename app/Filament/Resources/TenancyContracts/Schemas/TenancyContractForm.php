<?php

namespace App\Filament\Resources\TenancyContracts\Schemas;

use App\Models\Client;
use App\Models\PropertyUnit;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TenancyContractForm
{
    public static function configure(Schema $schema): Schema
    {
        $companyId = auth()->user()->company_id;

        return $schema
            ->components([
                Section::make('Informasi Kontrak')
                    ->schema([
                        Select::make('property_unit_id')
                            ->label('Unit Properti')
                            ->options(PropertyUnit::where('company_id', $companyId)->pluck('unit_number', 'id'))
                            ->searchable()
                            ->required(),
                        Select::make('client_id')
                            ->label('Penyewa')
                            ->options(Client::where('company_id', $companyId)->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        TextInput::make('contract_number')
                            ->label('Nomor Kontrak')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'active' => 'Aktif',
                                'expiring_soon' => 'Segera Habis',
                                'expired' => 'Habis',
                                'terminated' => 'Dihentikan',
                            ])
                            ->default('draft')
                            ->required(),
                    ])->columns(2),

                Section::make('Periode & Biaya')
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->required(),
                        DatePicker::make('end_date')
                            ->label('Tanggal Selesai')
                            ->required()
                            ->afterOrEqual('start_date'),
                        TextInput::make('monthly_rent')
                            ->label('Sewa Bulanan (Rp)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp'),
                        TextInput::make('deposit_amount')
                            ->label('Deposit (Rp)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp'),
                        TextInput::make('service_charge_monthly')
                            ->label('Service Charge/bulan (Rp)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp'),
                        TextInput::make('sinking_fund_monthly')
                            ->label('Sinking Fund/bulan (Rp)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp'),
                        TextInput::make('payment_due_day')
                            ->label('Jatuh Tempo (tanggal)')
                            ->integer()
                            ->minValue(1)
                            ->maxValue(31)
                            ->default(1),
                        TextInput::make('late_fee_percent')
                            ->label('Denda (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(5)
                            ->suffix('%'),
                        Toggle::make('renewal_option')
                            ->label('Opsi Perpanjangan'),
                    ])->columns(3),

                Section::make('Catatan')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}