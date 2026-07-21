<?php

namespace App\Filament\Resources\FieldService\WorkOrderResource\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Schema;

class WorkOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Work Order')
                    ->columns(3)
                    ->schema([
                        TextInput::make('wo_number')
                            ->label('No Work Order')
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn ($record) => $record !== null),
                        Select::make('client_id')
                            ->label('Klien')
                            ->relationship('client', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('service_contract_id')
                            ->label('Kontrak Layanan')
                            ->relationship('serviceContract', 'contract_number')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('equipment_id')
                            ->label('Peralatan')
                            ->relationship('equipment', 'equipment_name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('service_type')
                            ->label('Tipe Layanan')
                            ->required()
                            ->options([
                                'preventive' => 'Preventif',
                                'corrective' => 'Korektif',
                                'emergency' => 'Darurat',
                                'installation' => 'Instalasi',
                                'inspection' => 'Inspeksi',
                            ]),
                        Select::make('priority')
                            ->label('Prioritas')
                            ->required()
                            ->default('medium')
                            ->options([
                                'low' => 'Rendah',
                                'medium' => 'Sedang',
                                'high' => 'Tinggi',
                                'critical' => 'Kritis',
                            ]),
                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->default('open')
                            ->options([
                                'open' => 'Open',
                                'assigned' => 'Ditugaskan',
                                'en_route' => 'Di Perjalanan',
                                'in_progress' => 'Sedang Dikerjakan',
                                'completed' => 'Selesai',
                                'verified' => 'Terverifikasi',
                                'cancelled' => 'Dibatalkan',
                            ]),
                        Select::make('technician_id')
                            ->label('Teknisi')
                            ->relationship('technician', 'first_name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('helper_id')
                            ->label('Helper')
                            ->relationship('helper', 'first_name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('reported_by')
                            ->label('Dilaporkan Oleh')
                            ->maxLength(255),
                        DateTimePicker::make('scheduled_start')
                            ->label('Jadwal Mulai'),
                        DateTimePicker::make('scheduled_end')
                            ->label('Jadwal Selesai'),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Section::make('Biaya & Pengerjaan')
                    ->columns(3)
                    ->schema([
                        TextInput::make('service_charge')
                            ->label('Biaya Jasa')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        TextInput::make('parts_cost')
                            ->label('Biaya Suku Cadang')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->disabled(),
                        TextInput::make('total_cost')
                            ->label('Total Biaya')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->disabled(),
                        TextInput::make('travel_distance_km')
                            ->label('Jarak Tempuh (km)')
                            ->numeric()
                            ->disabled(),
                        TextInput::make('labor_hours')
                            ->label('Jam Kerja')
                            ->numeric()
                            ->disabled(),
                        TextInput::make('customer_rating')
                            ->label('Rating Pelanggan (1-5)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(5),
                        Textarea::make('resolution')
                            ->label('Resolusi')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('customer_feedback')
                            ->label('Feedback Pelanggan')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}