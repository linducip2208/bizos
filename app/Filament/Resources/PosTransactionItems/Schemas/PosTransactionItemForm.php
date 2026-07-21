<?php

namespace App\Filament\Resources\PosTransactionItems\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PosTransactionItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Item Transaksi POS')
                    ->columns(2)
                    ->schema([
                        Select::make('transaction_id')
                            ->label('Transaksi')
                            ->relationship('transaction', 'receipt_number')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('product_id')
                            ->label('Produk')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('variant_id')
                            ->label('Varian')
                            ->relationship('variant', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('quantity')
                            ->label('Jumlah')
                            ->required()
                            ->numeric()
                            ->default(1),
                        TextInput::make('unit_price')
                            ->label('Harga Satuan')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                        TextInput::make('discount_amount')
                            ->label('Diskon')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        TextInput::make('tax_amount')
                            ->label('Pajak')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                    ]),
            ]);
    }
}