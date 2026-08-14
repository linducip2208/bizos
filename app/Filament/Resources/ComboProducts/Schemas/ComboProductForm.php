<?php

namespace App\Filament\Resources\ComboProducts\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ComboProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Produk Kombo')
                    ->columns(2)
                    ->schema([
                        Select::make('product_id')
                            ->label('Produk')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama Kombo')
                            ->required()
                            ->maxLength(255),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
                Section::make('Komponen Kombo')
                    ->columns(1)
                    ->schema([
                        Repeater::make('items')
                            ->label('Komponen')
                            ->relationship()
                            ->schema([
                                Select::make('component_product_id')
                                    ->label('Produk Komponen')
                                    ->relationship('componentProduct', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                TextInput::make('quantity')
                                    ->label('Kuantitas')
                                    ->numeric()
                                    ->default(1)
                                    ->required(),
                            ])
                            ->columns(2)
                            ->columnSpanFull()
                            ->addActionLabel('Tambah Komponen')
                            ->defaultItems(1),
                    ]),
            ]);
    }
}
