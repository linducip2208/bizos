<?php

namespace App\Filament\Resources\Coupons\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kupon')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Kode')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
                        Select::make('promotion_id')
                            ->label('Promosi')
                            ->relationship('promotion', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('discount')
                            ->label('Diskon')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                        TextInput::make('min_purchase')
                            ->label('Min Pembelian')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        TextInput::make('max_uses')
                            ->label('Max Penggunaan')
                            ->numeric()
                            ->nullable(),
                        TextInput::make('used_count')
                            ->label('Terpakai')
                            ->numeric()
                            ->default(0)
                            ->disabled(),
                        DatePicker::make('valid_from')
                            ->label('Berlaku Dari')
                            ->required(),
                        DatePicker::make('valid_until')
                            ->label('Berlaku Sampai')
                            ->required()
                            ->after('valid_from'),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}
