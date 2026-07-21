<?php

namespace App\Filament\Resources\PurchaseOrderItems\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PurchaseOrderItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Item PO')
                    ->schema([
                        Select::make('purchase_order_id')
                            ->label('PO')
                            ->relationship('purchaseOrder', 'po_number')
                            ->preload()
                            ->searchable()
                            ->required(),
                        Select::make('product_id')
                            ->label('Produk')
                            ->relationship('product', 'name')
                            ->preload()
                            ->searchable()
                            ->nullable()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $product = \App\Models\Product::find($state);
                                    if ($product) {
                                        $set('item_name', $product->name);
                                        $set('unit', $product->unit ?? 'pcs');
                                    }
                                }
                            }),
                        Select::make('pr_item_id')
                            ->label('Item PR')
                            ->relationship('prItem', 'item_name')
                            ->preload()
                            ->searchable()
                            ->nullable(),
                        TextInput::make('item_name')
                            ->label('Nama Item')
                            ->required()
                            ->maxLength(300),
                        TextInput::make('specification')
                            ->label('Spesifikasi')
                            ->maxLength(500),
                        TextInput::make('unit')
                            ->label('Satuan')
                            ->required()
                            ->maxLength(50)
                            ->default('pcs'),
                        TextInput::make('quantity')
                            ->label('Kuantitas')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('received_qty')
                            ->label('Qty Diterima')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('unit_price')
                            ->label('Harga Satuan')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                        TextInput::make('tax_rate')
                            ->label('Tarif Pajak (%)')
                            ->numeric()
                            ->default(11)
                            ->suffix('%'),
                        Toggle::make('is_taxable')
                            ->label('Kena Pajak')
                            ->default(true),
                        TextInput::make('discount_percent')
                            ->label('Diskon (%)')
                            ->numeric()
                            ->default(0)
                            ->suffix('%'),
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->dehydrated(),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }
}
