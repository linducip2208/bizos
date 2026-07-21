<?php

namespace App\Filament\Resources\LoyaltyConfigs\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LoyaltyConfigForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Loyalitas')
                    ->columns(3)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('earn_rate')
                            ->label('Tingkat Perolehan Poin')
                            ->numeric()
                            ->inputMode('decimal')
                            ->suffix('%')
                            ->required()
                            ->helperText('Persentase poin yang didapat dari total belanja'),
                        TextInput::make('redeem_rate')
                            ->label('Tingkat Penukaran (Rp per poin)')
                            ->numeric()
                            ->inputMode('decimal')
                            ->prefix('Rp')
                            ->required()
                            ->helperText('Nilai rupiah per 1 poin saat ditukarkan'),
                        TextInput::make('points_expiry_months')
                            ->label('Masa Berlaku Poin (bulan)')
                            ->numeric()
                            ->integer()
                            ->required()
                            ->default(12),
                        TextInput::make('silver_threshold')
                            ->label('Ambang Silver (poin)')
                            ->numeric()
                            ->integer()
                            ->required()
                            ->default(100),
                        TextInput::make('gold_threshold')
                            ->label('Ambang Gold (poin)')
                            ->numeric()
                            ->integer()
                            ->required()
                            ->default(500),
                        TextInput::make('platinum_threshold')
                            ->label('Ambang Platinum (poin)')
                            ->numeric()
                            ->integer()
                            ->required()
                            ->default(1000),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}