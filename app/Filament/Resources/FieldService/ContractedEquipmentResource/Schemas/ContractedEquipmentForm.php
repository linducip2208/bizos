<?php

namespace App\Filament\Resources\FieldService\ContractedEquipmentResource\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;

class ContractedEquipmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Peralatan')
                    ->columns(2)
                    ->schema([
                        Select::make('service_contract_id')
                            ->label('Kontrak Layanan')
                            ->relationship('serviceContract', 'contract_number')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('equipment_name')
                            ->label('Nama Peralatan')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('brand')
                            ->label('Merek')
                            ->maxLength(100),
                        TextInput::make('model')
                            ->label('Model')
                            ->maxLength(100),
                        TextInput::make('serial_number')
                            ->label('Nomor Seri')
                            ->maxLength(100),
                        TextInput::make('location')
                            ->label('Lokasi')
                            ->maxLength(255),
                        DatePicker::make('installation_date')
                            ->label('Tanggal Instalasi'),
                        DatePicker::make('warranty_expiry')
                            ->label('Garansi Berakhir'),
                        DatePicker::make('last_service_date')
                            ->label('Service Terakhir'),
                        DatePicker::make('next_service_date')
                            ->label('Service Berikutnya'),
                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->default('active')
                            ->options([
                                'active' => 'Aktif',
                                'under_repair' => 'Dalam Perbaikan',
                                'decommissioned' => 'Nonaktif',
                            ]),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}