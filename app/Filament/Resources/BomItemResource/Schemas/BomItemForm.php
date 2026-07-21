<?php

namespace App\Filament\Resources\BomItemResource\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BomItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Item BOM')
                    ->columns(2)
                    ->schema([
                        Select::make('bom_id')
                            ->label('Bill of Material')
                            ->relationship('bom', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('product_id')
                            ->label('Bahan Baku / Komponen')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('quantity_per_unit')
                            ->label('Kuantitas per Unit')
                            ->numeric()
                            ->inputMode('decimal')
                            ->required(),
                        TextInput::make('unit')
                            ->label('Satuan')
                            ->default('pcs')
                            ->maxLength(20),
                        TextInput::make('scrap_percent')
                            ->label('Scrap (%)')
                            ->numeric()
                            ->inputMode('decimal')
                            ->default(0)
                            ->suffix('%'),
                        TextInput::make('sort_order')
                            ->label('Urutan')
                            ->integer()
                            ->default(0),
                        Toggle::make('is_critical')
                            ->label('Kritis (untuk MRP)')
                            ->default(false),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
