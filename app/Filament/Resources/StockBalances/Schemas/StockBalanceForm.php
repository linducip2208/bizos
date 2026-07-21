<?php

namespace App\Filament\Resources\StockBalances\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StockBalanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Saldo Stok')
                    ->schema([
                        Select::make('product_id')
                            ->label('Produk')
                            ->relationship('product', 'name')
                            ->preload()
                            ->searchable()
                            ->required(),
                        Select::make('product_variant_id')
                            ->label('Varian')
                            ->relationship('variant', 'name')
                            ->preload()
                            ->searchable()
                            ->nullable(),
                        Select::make('warehouse_id')
                            ->label('Gudang')
                            ->relationship('warehouse', 'name')
                            ->preload()
                            ->searchable()
                            ->required(),
                        TextInput::make('quantity')
                            ->label('Kuantitas')
                            ->required()
                            ->numeric()
                            ->default(0),
                        TextInput::make('average_cost')
                            ->label('Biaya Rata-rata')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        TextInput::make('last_cost')
                            ->label('Biaya Terakhir')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                    ])->columns(3),
            ]);
    }
}
