<?php

namespace App\Filament\Resources\WasteLogs\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class WasteLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Waste Log')
                    ->columns(2)
                    ->schema([
                        Select::make('production_order_id')
                            ->label('Production Order')
                            ->relationship('productionOrder', 'po_number')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('product_id')
                            ->label('Produk')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('quantity')
                            ->label('Kuantitas')
                            ->numeric()
                            ->inputMode('decimal')
                            ->required(),
                        TextInput::make('unit')
                            ->label('Satuan')
                            ->default('pcs')
                            ->maxLength(20),
                        Select::make('waste_type')
                            ->label('Tipe Waste')
                            ->options([
                                'scrap' => 'Scrap (Sisa)',
                                'rework' => 'Rework (Pengerjaan Ulang)',
                                'reject' => 'Reject (Tolak)',
                            ])
                            ->required()
                            ->default('scrap'),
                        TextInput::make('cost_impact')
                            ->label('Dampak Biaya (Rp)')
                            ->numeric()
                            ->inputMode('decimal')
                            ->prefix('Rp')
                            ->default(0),
                        Select::make('reported_by')
                            ->label('Dilaporkan Oleh')
                            ->relationship('reporter', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Textarea::make('reason')
                            ->label('Alasan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}