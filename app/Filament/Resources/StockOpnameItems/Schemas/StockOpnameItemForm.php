<?php

namespace App\Filament\Resources\StockOpnameItems\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Placeholder;
use Filament\Schemas\Schema;

class StockOpnameItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Item Opname')
                    ->schema([
                        Select::make('stock_opname_id')
                            ->label('No. Opname')
                            ->relationship('stockOpname', 'opname_number')
                            ->preload()
                            ->searchable()
                            ->required(),
                        Select::make('product_id')
                            ->label('Produk')
                            ->relationship('product', 'name')
                            ->preload()
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if ($state) {
                                    $stockOpnameId = $get('stock_opname_id');
                                    if ($stockOpnameId) {
                                        $stockOpname = \App\Models\StockOpname::find($stockOpnameId);
                                        if ($stockOpname) {
                                            $balance = \App\Models\StockBalance::where('product_id', $state)
                                                ->where('warehouse_id', $stockOpname->warehouse_id)
                                                ->first();
                                            if ($balance) {
                                                $set('system_quantity', $balance->quantity);
                                                $set('unit_cost', $balance->average_cost);
                                            }
                                        }
                                    }
                                }
                            }),
                        Select::make('product_variant_id')
                            ->label('Varian')
                            ->relationship('variant', 'name')
                            ->preload()
                            ->searchable()
                            ->nullable(),
                        TextInput::make('system_quantity')
                            ->label('Qty Sistem')
                            ->required()
                            ->numeric()
                            ->default(0),
                        TextInput::make('physical_quantity')
                            ->label('Qty Fisik')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $system = $get('system_quantity') ?? 0;
                                $set('difference', ($state ?? 0) - $system);
                            }),
                        TextInput::make('difference')
                            ->label('Selisih')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->color(fn ($state) => $state != 0 ? 'danger' : 'success'),
                        TextInput::make('unit_cost')
                            ->label('Harga Satuan')
                            ->numeric()
                            ->prefix('Rp')
                            ->nullable(),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])->columns(3),
            ]);
    }
}