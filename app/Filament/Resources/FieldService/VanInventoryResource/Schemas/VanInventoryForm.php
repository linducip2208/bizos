<?php

namespace App\Filament\Resources\FieldService\VanInventoryResource\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;

class VanInventoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Stok Van')
                    ->columns(2)
                    ->schema([
                        Select::make('van_id')
                            ->label('Van Teknisi')
                            ->relationship('van', 'license_plate')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('product_id')
                            ->label('Produk')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('quantity')
                            ->label('Jumlah')
                            ->numeric()
                            ->required()
                            ->default(0),
                        TextInput::make('min_quantity')
                            ->label('Minimal')
                            ->numeric()
                            ->default(0),
                        TextInput::make('reorder_point')
                            ->label('Reorder Point')
                            ->numeric()
                            ->default(0),
                        DatePicker::make('last_restock_date')
                            ->label('Restock Terakhir'),
                    ]),
            ]);
    }
}
