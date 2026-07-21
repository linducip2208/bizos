<?php

namespace App\Filament\Resources\AssetDepreciation\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AssetDepreciationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Penyusutan Aset')
                    ->columns(2)
                    ->schema([
                        Select::make('asset_id')
                            ->label('Aset')
                            ->relationship('asset', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('year')
                            ->label('Tahun')
                            ->numeric()
                            ->required()
                            ->minValue(2000)
                            ->maxValue(2100),
                        TextInput::make('month')
                            ->label('Bulan')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(12),
                        TextInput::make('depreciation_amount')
                            ->label('Jumlah Penyusutan')
                            ->numeric()
                            ->required(),
                        TextInput::make('accumulated_before')
                            ->label('Akumulasi Sebelumnya')
                            ->numeric()
                            ->required(),
                        TextInput::make('accumulated_after')
                            ->label('Akumulasi Setelah')
                            ->numeric()
                            ->required(),
                        TextInput::make('book_value_after')
                            ->label('Nilai Buku Setelah')
                            ->numeric()
                            ->required(),
                        Select::make('journal_id')
                            ->label('Jurnal')
                            ->relationship('journal', 'journal_number')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ]),
            ]);
    }
}
