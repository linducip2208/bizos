<?php

namespace App\Filament\Resources\CommissionSlabs\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CommissionSlabForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Komisi Slab')
                    ->columns(3)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('min_amount')
                            ->label('Minimal Nominal (Rp)')
                            ->numeric()
                            ->inputMode('decimal')
                            ->prefix('Rp')
                            ->required()
                            ->helperText('Batas bawah nilai transaksi'),
                        TextInput::make('max_amount')
                            ->label('Maksimal Nominal (Rp)')
                            ->numeric()
                            ->inputMode('decimal')
                            ->prefix('Rp')
                            ->required()
                            ->helperText('Batas atas nilai transaksi, kosongkan untuk tanpa batas'),
                        TextInput::make('rate_percent')
                            ->label('Persentase Komisi')
                            ->numeric()
                            ->inputMode('decimal')
                            ->suffix('%')
                            ->required()
                            ->helperText('Persentase komisi dari nilai transaksi'),
                        TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->integer()
                            ->default(0),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}
