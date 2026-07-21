<?php

namespace App\Filament\Resources\GoodsReceiptItems\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GoodsReceiptItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Item Penerimaan')
                    ->schema([
                        Select::make('goods_receipt_id')
                            ->label('No. GRN')
                            ->relationship('goodsReceipt', 'grn_number')
                            ->preload()
                            ->searchable()
                            ->required(),
                        Select::make('po_item_id')
                            ->label('Item PO')
                            ->relationship('poItem', 'item_name')
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
                        TextInput::make('item_name')
                            ->label('Nama Item')
                            ->required()
                            ->maxLength(300),
                        TextInput::make('unit')
                            ->label('Satuan')
                            ->required()
                            ->maxLength(50)
                            ->default('pcs'),
                        TextInput::make('quantity_received')
                            ->label('Qty Diterima')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('quantity_accepted')
                            ->label('Qty Diterima Baik')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('quantity_rejected')
                            ->label('Qty Ditolak')
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('unit_price')
                            ->label('Harga Satuan')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }
}
