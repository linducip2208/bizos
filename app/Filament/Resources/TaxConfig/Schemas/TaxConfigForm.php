<?php

namespace App\Filament\Resources\TaxConfig\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TaxConfigForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Konfigurasi Pajak')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('tax_type')
                            ->label('Jenis Pajak')
                            ->options([
                                'ppn' => 'PPN',
                                'pph21' => 'PPh 21',
                                'pph22' => 'PPh 22',
                                'pph23' => 'PPh 23',
                                'pph25' => 'PPh 25',
                                'pph29' => 'PPh 29',
                                'pph_final' => 'PPh Final',
                            ])
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama Konfigurasi')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('rate')
                            ->label('Tarif (desimal)')
                            ->numeric()
                            ->required()
                            ->helperText('Contoh: 0.11 untuk 11%'),
                        TextInput::make('effective_year')
                            ->label('Tahun Berlaku')
                            ->numeric()
                            ->required(),
                    ]),
                Section::make('Status')
                    ->columns(1)
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}
