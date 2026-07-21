<?php

namespace App\Filament\Resources\ProductDiscounts\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductDiscountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Diskon Produk')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama Diskon')
                            ->required()
                            ->maxLength(255),
                        Select::make('type')
                            ->label('Tipe Diskon')
                            ->required()
                            ->default('percentage')
                            ->options([
                                'percentage' => 'Persentase',
                                'fixed' => 'Nominal',
                            ]),
                        TextInput::make('value')
                            ->label('Nilai')
                            ->required()
                            ->numeric()
                            ->prefix(fn ($get) => $get('type') === 'percentage' ? '' : 'Rp')
                            ->suffix(fn ($get) => $get('type') === 'percentage' ? '%' : ''),
                        TextInput::make('min_purchase')
                            ->label('Minimal Pembelian')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        DatePicker::make('start_date')
                            ->label('Tanggal Mulai'),
                        DatePicker::make('end_date')
                            ->label('Tanggal Selesai'),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}