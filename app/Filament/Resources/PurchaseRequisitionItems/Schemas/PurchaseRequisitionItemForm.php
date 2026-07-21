<?php

namespace App\Filament\Resources\PurchaseRequisitionItems\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PurchaseRequisitionItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Item PR')
                    ->schema([
                        Select::make('purchase_requisition_id')
                            ->label('PR')
                            ->relationship('purchaseRequisition', 'pr_number')
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
                        TextInput::make('estimated_price')
                            ->label('Estimasi Harga Satuan')
                            ->numeric()
                            ->prefix('Rp')
                            ->nullable(),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }
}