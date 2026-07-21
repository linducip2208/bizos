<?php

namespace App\Filament\Resources\FinishedGoods\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FinishedGoodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Barang Jadi')
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
                            ->required(),
                        TextInput::make('quantity')
                            ->label('Kuantitas')
                            ->numeric()
                            ->inputMode('decimal')
                            ->required()
                            ->default(0),
                        DateTimePicker::make('accepted_at')
                            ->label('Diterima Pada')
                            ->default(now()),
                        Select::make('quality_status')
                            ->label('Status Kualitas')
                            ->options([
                                'passed' => 'Lolos QC',
                                'failed' => 'Gagal QC',
                                'rework' => 'Perlu Rework',
                            ])
                            ->required()
                            ->default('passed'),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
