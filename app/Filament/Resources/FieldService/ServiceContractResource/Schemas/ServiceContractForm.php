<?php

namespace App\Filament\Resources\FieldService\ServiceContractResource\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;

class ServiceContractForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kontrak')
                    ->columns(2)
                    ->schema([
                        TextInput::make('contract_number')
                            ->label('Nomor Kontrak')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        Select::make('client_id')
                            ->label('Klien')
                            ->relationship('client', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('contract_type')
                            ->label('Tipe Kontrak')
                            ->required()
                            ->options([
                                'maintenance_regular' => 'Maintenance Reguler',
                                'maintenance_comprehensive' => 'Maintenance Komprehensif',
                                'installation' => 'Instalasi',
                                'repair' => 'Perbaikan',
                            ]),
                        Select::make('billing_cycle')
                            ->label('Siklus Penagihan')
                            ->required()
                            ->options([
                                'monthly' => 'Bulanan',
                                'quarterly' => 'Kuartalan',
                                'annually' => 'Tahunan',
                            ]),
                        TextInput::make('billing_amount')
                            ->label('Jumlah Penagihan')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                        Select::make('service_frequency')
                            ->label('Frekuensi Layanan')
                            ->required()
                            ->options([
                                'weekly' => 'Mingguan',
                                'biweekly' => 'Dua Mingguan',
                                'monthly' => 'Bulanan',
                                'quarterly' => 'Kuartalan',
                            ]),
                        TextInput::make('equipment_count')
                            ->label('Jumlah Peralatan')
                            ->numeric()
                            ->default(0),
                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->default('draft')
                            ->options([
                                'draft' => 'Draft',
                                'active' => 'Aktif',
                                'suspended' => 'Ditangguhkan',
                                'expired' => 'Kadaluarsa',
                                'terminated' => 'Dihentikan',
                            ]),
                        DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->required(),
                        DatePicker::make('end_date')
                            ->label('Tanggal Berakhir')
                            ->nullable(),
                    ]),
                Section::make('SLA & Lainnya')
                    ->columns(2)
                    ->schema([
                        TextInput::make('sla_response_hours')
                            ->label('SLA Response (Jam)')
                            ->numeric()
                            ->default(4),
                        TextInput::make('sla_resolution_hours')
                            ->label('SLA Resolusi (Jam)')
                            ->numeric()
                            ->default(24),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
