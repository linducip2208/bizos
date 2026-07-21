<?php

namespace App\Filament\Resources\AssetCategory\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AssetCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kategori Aset')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('code')
                            ->label('Kode')
                            ->required()
                            ->maxLength(50),
                        TextInput::make('name')
                            ->label('Nama Kategori')
                            ->required()
                            ->maxLength(255),
                        Select::make('depreciation_method')
                            ->label('Metode Penyusutan')
                            ->options([
                                'straight_line' => 'Garis Lurus',
                                'declining_balance' => 'Saldo Menurun',
                                'sum_of_years' => 'Jumlah Tahun',
                                'units_of_production' => 'Unit Produksi',
                                'none' => 'Tanpa Penyusutan',
                            ])
                            ->required()
                            ->default('straight_line'),
                        TextInput::make('useful_life_years')
                            ->label('Masa Manfaat (Tahun)')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                        TextInput::make('salvage_value_percent')
                            ->label('Nilai Residu (%)')
                            ->numeric()
                            ->default(0)
                            ->suffix('%'),
                    ]),
            ]);
    }
}
