<?php

namespace App\Filament\Resources\EcommerceOrderItemResource\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EcommerceOrderItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Item Pesanan')
                    ->columns(2)
                    ->schema([
                        Select::make('ecommerce_order_id')
                            ->label('Pesanan')
                            ->relationship('ecommerceOrder', 'channel_order_id')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('channel_sku')
                            ->label('Channel SKU')
                            ->required()
                            ->maxLength(100),
                        Select::make('product_id')
                            ->label('Produk (BizOS)')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('product_name')
                            ->label('Nama Produk')
                            ->required()
                            ->maxLength(200),
                        TextInput::make('quantity')
                            ->label('Jumlah')
                            ->numeric()
                            ->required()
                            ->minValue(0.01),
                        TextInput::make('unit_price')
                            ->label('Harga Satuan')
                            ->numeric()
                            ->required(),
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->required(),
                    ]),
            ]);
    }
}
