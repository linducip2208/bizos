<?php

namespace App\Filament\Resources\SerialNumbers\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SerialNumberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Nomor Seri')
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
                        TextInput::make('serial_number')
                            ->label('Nomor Seri')
                            ->required()
                            ->maxLength(100),
                        Select::make('batch_id')
                            ->label('Batch')
                            ->relationship('batch', 'batch_number')
                            ->searchable()
                            ->preload(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'available' => 'Tersedia',
                                'sold' => 'Terjual',
                                'returned' => 'Dikembalikan',
                                'damaged' => 'Rusak',
                            ])
                            ->required()
                            ->default('available'),
                    ]),
            ]);
    }
}
