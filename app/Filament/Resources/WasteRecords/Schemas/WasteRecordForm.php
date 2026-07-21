<?php

namespace App\Filament\Resources\WasteRecords\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WasteRecordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Data Limbah')
                    ->columns(3)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('branch_id')
                            ->label('Cabang')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        DatePicker::make('record_date')
                            ->label('Tanggal')
                            ->required()
                            ->default(now()),
                        Select::make('waste_type')
                            ->label('Jenis Limbah')
                            ->options([
                                'hazardous' => 'B3 (Berbahaya)',
                                'solid' => 'Padat',
                                'liquid' => 'Cair',
                                'organic' => 'Organik',
                                'recyclable' => 'Dapat Didaur Ulang',
                                'electronic' => 'Elektronik',
                            ])
                            ->required(),
                        TextInput::make('waste_subtype')
                            ->label('Sub-Jenis')
                            ->maxLength(100)
                            ->nullable(),
                        TextInput::make('quantity_kg')
                            ->label('Jumlah (kg)')
                            ->numeric()
                            ->required(),
                        Select::make('source')
                            ->label('Sumber')
                            ->options([
                                'production' => 'Produksi',
                                'office' => 'Kantor',
                                'canteen' => 'Kantin',
                                'warehouse' => 'Gudang',
                                'construction' => 'Konstruksi',
                            ])
                            ->nullable(),
                        Select::make('disposal_method')
                            ->label('Metode Pembuangan')
                            ->options([
                                'landfill' => 'TPA',
                                'incinerated' => 'Insinerasi',
                                'recycled' => 'Daur Ulang',
                                'composted' => 'Kompos',
                                'treated_offsite' => 'Pengolahan Pihak Ketiga',
                            ])
                            ->required(),
                        TextInput::make('disposal_vendor')
                            ->label('Vendor Pembuangan')
                            ->maxLength(200)
                            ->nullable(),
                        TextInput::make('disposal_cost')
                            ->label('Biaya Pembuangan (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->nullable(),
                        Toggle::make('is_hazardous')
                            ->label('Limbah B3')
                            ->default(false),
                        TextInput::make('manifest_number')
                            ->label('Nomor Manifest (B3)')
                            ->maxLength(100)
                            ->nullable(),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(2)
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}