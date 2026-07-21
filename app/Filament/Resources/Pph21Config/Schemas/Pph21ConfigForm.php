<?php

namespace App\Filament\Resources\Pph21Config\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class Pph21ConfigForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Konfigurasi PPh 21')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('ptkp_category')
                            ->label('Kategori PTKP')
                            ->options([
                                'TK0' => 'TK/0 - Tidak Kawin Tanpa Tanggungan',
                                'TK1' => 'TK/1 - Tidak Kawin 1 Tanggungan',
                                'TK2' => 'TK/2 - Tidak Kawin 2 Tanggungan',
                                'TK3' => 'TK/3 - Tidak Kawin 3 Tanggungan',
                                'K0' => 'K/0 - Kawin Tanpa Tanggungan',
                                'K1' => 'K/1 - Kawin 1 Tanggungan',
                                'K2' => 'K/2 - Kawin 2 Tanggungan',
                                'K3' => 'K/3 - Kawin 3 Tanggungan',
                            ])
                            ->required(),
                        TextInput::make('ptkp_amount')
                            ->label('PTKP (Rp)')
                            ->numeric()
                            ->required(),
                        TextInput::make('threshold_low')
                            ->label('Ambang Rendah (Rp)')
                            ->numeric()
                            ->required(),
                        TextInput::make('rate_low')
                            ->label('Tarif Rendah (%)')
                            ->numeric()
                            ->inputMode('decimal')
                            ->required(),
                        TextInput::make('threshold_mid')
                            ->label('Ambang Menengah (Rp)')
                            ->numeric()
                            ->required(),
                        TextInput::make('rate_mid')
                            ->label('Tarif Menengah (%)')
                            ->numeric()
                            ->inputMode('decimal')
                            ->required(),
                        TextInput::make('threshold_high')
                            ->label('Ambang Tinggi (Rp)')
                            ->numeric()
                            ->required(),
                        TextInput::make('rate_high')
                            ->label('Tarif Tinggi (%)')
                            ->numeric()
                            ->inputMode('decimal')
                            ->required(),
                        TextInput::make('rate_top')
                            ->label('Tarif Tertinggi (%)')
                            ->numeric()
                            ->inputMode('decimal')
                            ->required(),
                        TextInput::make('effective_year')
                            ->label('Tahun Berlaku')
                            ->numeric()
                            ->required()
                            ->minValue(2000)
                            ->maxValue(2100),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}
