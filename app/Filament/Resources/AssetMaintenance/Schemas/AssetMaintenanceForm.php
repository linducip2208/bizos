<?php

namespace App\Filament\Resources\AssetMaintenance\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AssetMaintenanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pemeliharaan Aset')
                    ->columns(2)
                    ->schema([
                        Select::make('asset_id')
                            ->label('Aset')
                            ->relationship('asset', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('maintenance_type')
                            ->label('Tipe Pemeliharaan')
                            ->options([
                                'preventive' => 'Preventif',
                                'corrective' => 'Korektif',
                                'inspection' => 'Inspeksi',
                                'overhaul' => 'Overhaul',
                            ])
                            ->required(),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('cost')
                            ->label('Biaya')
                            ->numeric()
                            ->default(0),
                        DatePicker::make('scheduled_date')
                            ->label('Tanggal Dijadwalkan')
                            ->required(),
                        DatePicker::make('completed_date')
                            ->label('Tanggal Selesai')
                            ->nullable(),
                        TextInput::make('vendor_name')
                            ->label('Nama Vendor')
                            ->maxLength(255)
                            ->nullable(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'scheduled' => 'Dijadwalkan',
                                'in_progress' => 'Dalam Proses',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->required()
                            ->default('scheduled'),
                    ]),
            ]);
    }
}