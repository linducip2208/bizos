<?php

namespace App\Filament\Resources\ServiceChargeInvoices\Schemas;

use App\Models\PropertyUnit;
use App\Models\TenancyContract;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceChargeInvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        $companyId = auth()->user()->company_id;

        return $schema
            ->components([
                Section::make('Referensi')
                    ->schema([
                        Select::make('property_unit_id')
                            ->label('Unit Properti')
                            ->options(PropertyUnit::where('company_id', $companyId)->pluck('unit_number', 'id'))
                            ->searchable()
                            ->required(),
                        Select::make('tenancy_contract_id')
                            ->label('Kontrak Sewa')
                            ->options(
                                TenancyContract::where('company_id', $companyId)
                                    ->with('client')
                                    ->get()
                                    ->mapWithKeys(fn ($c) => [
                                        $c->id => "{$c->contract_number} - {$c->client->name}"
                                    ])
                            )
                            ->searchable()
                            ->required(),
                        TextInput::make('invoice_number')
                            ->label('Nomor Invoice')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        DatePicker::make('period_start')
                            ->label('Periode Mulai')
                            ->required(),
                        DatePicker::make('period_end')
                            ->label('Periode Selesai')
                            ->required()
                            ->afterOrEqual('period_start'),
                    ])->columns(2),

                Section::make('Rincian Biaya')
                    ->schema([
                        TextInput::make('rent_amount')
                            ->label('Sewa (Rp)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp'),
                        TextInput::make('service_charge')
                            ->label('Service Charge (Rp)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp'),
                        TextInput::make('sinking_fund')
                            ->label('Sinking Fund (Rp)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp'),
                        TextInput::make('electricity')
                            ->label('Listrik (Rp)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp'),
                        TextInput::make('water')
                            ->label('Air (Rp)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp'),
                        TextInput::make('other_charges')
                            ->label('Biaya Lain (Rp)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp'),
                        TextInput::make('total_amount')
                            ->label('Total (Rp)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp'),
                    ])->columns(3),

                Section::make('Pembayaran')
                    ->schema([
                        DatePicker::make('due_date')
                            ->label('Jatuh Tempo')
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'unpaid' => 'Belum Dibayar',
                                'paid' => 'Dibayar',
                                'overdue' => 'Terlambat',
                            ])
                            ->default('unpaid')
                            ->required(),
                    ])->columns(2),
            ]);
    }
}
