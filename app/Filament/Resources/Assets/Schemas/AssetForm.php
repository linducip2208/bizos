<?php

namespace App\Filament\Resources\Assets\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class AssetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Aset')
                    ->columns(3)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('asset_code')
                            ->label('Kode Aset')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('name')
                            ->label('Nama Aset')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
                Section::make('Perolehan & Penyusutan')
                    ->columns(3)
                    ->schema([
                        DatePicker::make('acquisition_date')
                            ->label('Tanggal Perolehan')
                            ->required(),
                        TextInput::make('acquisition_cost')
                            ->label('Harga Perolehan')
                            ->numeric()
                            ->required()
                            ->prefix('Rp'),
                        TextInput::make('useful_life_years')
                            ->label('Masa Manfaat (Tahun)')
                            ->numeric()
                            ->nullable(),
                        TextInput::make('salvage_value')
                            ->label('Nilai Residu')
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp'),
                    ]),
                Section::make('Lokasi & Penanggung Jawab')
                    ->columns(3)
                    ->schema([
                        TextInput::make('location')
                            ->label('Lokasi')
                            ->maxLength(255)
                            ->nullable(),
                        Select::make('current_employee_id')
                            ->label('Pemegang Saat Ini')
                            ->relationship('currentEmployee', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => trim($record->first_name . ' ' . $record->last_name))
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Aktif',
                                'maintenance' => 'Dalam Perbaikan',
                                'disposed' => 'Dijual/Dihapus',
                                'idle' => 'Menganggur',
                                'transferred' => 'Dipindahkan',
                            ])
                            ->default('active')
                            ->required(),
                    ]),
            ]);
    }
}