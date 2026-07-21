<?php

namespace App\Filament\Resources\StockMovements\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StockMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pergerakan')
                    ->schema([
                        Select::make('product_id')
                            ->label('Produk')
                            ->relationship('product', 'name')
                            ->preload()
                            ->searchable()
                            ->required(),
                        Select::make('product_variant_id')
                            ->label('Varian')
                            ->relationship('variant', 'name')
                            ->preload()
                            ->searchable()
                            ->nullable(),
                        Select::make('warehouse_id')
                            ->label('Gudang')
                            ->relationship('warehouse', 'name')
                            ->preload()
                            ->searchable()
                            ->required(),
                        Select::make('movement_type')
                            ->label('Tipe Pergerakan')
                            ->options([
                                'in' => 'Masuk',
                                'out' => 'Keluar',
                                'transfer' => 'Transfer',
                                'adjustment' => 'Penyesuaian',
                                'opname' => 'Opname',
                                'return' => 'Retur',
                            ])
                            ->required(),
                        TextInput::make('reference_type')
                            ->label('Tipe Referensi')
                            ->maxLength(50),
                        TextInput::make('reference_id')
                            ->label('ID Referensi')
                            ->numeric(),
                        DateTimePicker::make('movement_date')
                            ->label('Tanggal Pergerakan')
                            ->required()
                            ->default(now()),
                    ])->columns(3),

                Section::make('Kuantitas & Biaya')
                    ->schema([
                        TextInput::make('quantity_in')
                            ->label('Qty Masuk')
                            ->numeric()
                            ->default(0),
                        TextInput::make('quantity_out')
                            ->label('Qty Keluar')
                            ->numeric()
                            ->default(0),
                        TextInput::make('unit_cost')
                            ->label('Harga Satuan')
                            ->numeric()
                            ->prefix('Rp')
                            ->nullable(),
                        TextInput::make('running_quantity')
                            ->label('Qty Berjalan')
                            ->numeric()
                            ->required(),
                        TextInput::make('running_cost')
                            ->label('Biaya Berjalan')
                            ->numeric()
                            ->prefix('Rp')
                            ->nullable(),
                    ])->columns(3),

                Section::make('Informasi Tambahan')
                    ->schema([
                        Select::make('created_by')
                            ->label('Dibuat Oleh')
                            ->relationship('creator', 'full_name')
                            ->preload()
                            ->searchable()
                            ->required(),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])->columns(1),
            ]);
    }
}