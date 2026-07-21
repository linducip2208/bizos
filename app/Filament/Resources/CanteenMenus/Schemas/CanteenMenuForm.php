<?php

namespace App\Filament\Resources\CanteenMenus\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CanteenMenuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Menu')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama Menu')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('category')
                            ->label('Kategori')
                            ->datalist([
                                'Makanan',
                                'Minuman',
                                'Snack',
                                'Rokok',
                            ])
                            ->required()
                            ->maxLength(100),
                        TextInput::make('price')
                            ->label('Harga')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                        TextInput::make('stock')
                            ->label('Stok')
                            ->numeric()
                            ->default(0),
                        FileUpload::make('photo')
                            ->label('Foto')
                            ->image()
                            ->directory('canteen-menus')
                            ->maxSize(2048)
                            ->nullable()
                            ->columnSpanFull(),
                        Toggle::make('is_available')
                            ->label('Tersedia')
                            ->default(true),
                    ]),
            ]);
    }
}
