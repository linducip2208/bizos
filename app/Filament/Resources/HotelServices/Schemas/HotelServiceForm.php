<?php

namespace App\Filament\Resources\HotelServices\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HotelServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Layanan')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Layanan')
                            ->required()
                            ->maxLength(200),
                        Select::make('category')
                            ->label('Kategori')
                            ->options([
                                'restaurant' => 'Restoran',
                                'laundry' => 'Laundry',
                                'spa' => 'Spa',
                                'transport' => 'Transport',
                                'minibar' => 'Minibar',
                                'phone' => 'Telepon',
                                'other' => 'Lainnya',
                            ])
                            ->default('other')
                            ->required(),
                        TextInput::make('unit_price')
                            ->label('Harga Satuan (Rp)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp'),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ])->columns(2),
            ]);
    }
}
