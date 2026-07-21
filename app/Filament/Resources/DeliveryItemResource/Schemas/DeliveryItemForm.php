<?php

namespace App\Filament\Resources\DeliveryItemResource\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class DeliveryItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Item Surat Jalan')
                    ->columns(2)
                    ->schema([
                        Select::make('delivery_order_id')
                            ->label('Surat Jalan')
                            ->relationship('deliveryOrder', 'do_number')
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
                            ->minValue(0.01),
                        TextInput::make('unit')
                            ->label('Satuan')
                            ->default('pcs')
                            ->maxLength(20),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(2)
                            ->nullable(),
                    ]),
            ]);
    }
}
