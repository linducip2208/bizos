<?php

namespace App\Filament\Resources\CustomerGroups\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CustomerGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Grup Pelanggan')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),
                        Select::make('price_list_id')
                            ->label('Daftar Harga')
                            ->relationship('priceList', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('discount_percent')
                            ->label('Diskon (%)')
                            ->numeric()
                            ->suffix('%')
                            ->nullable(),
                        TextInput::make('credit_limit')
                            ->label('Batas Kredit')
                            ->numeric()
                            ->prefix('Rp')
                            ->nullable(),
                        TextInput::make('payment_terms')
                            ->label('Syarat Pembayaran')
                            ->maxLength(255)
                            ->nullable(),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
