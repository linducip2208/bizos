<?php

namespace App\Filament\Resources\Batches\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Batch')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('product_id')
                            ->label('Produk')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('batch_number')
                            ->label('Nomor Batch')
                            ->required()
                            ->maxLength(100),
                        DatePicker::make('manufacturing_date')
                            ->label('Tanggal Produksi'),
                        DatePicker::make('expiry_date')
                            ->label('Tanggal Kedaluwarsa'),
                        TextInput::make('quantity')
                            ->label('Kuantitas')
                            ->numeric()
                            ->inputMode('decimal')
                            ->required()
                            ->default(0),
                        TextInput::make('unit_cost')
                            ->label('Biaya per Unit (Rp)')
                            ->numeric()
                            ->inputMode('decimal')
                            ->prefix('Rp'),
                        Select::make('warehouse_id')
                            ->label('Gudang')
                            ->relationship('warehouse', 'name')
                            ->searchable()
                            ->preload(),
                    ]),
            ]);
    }
}
