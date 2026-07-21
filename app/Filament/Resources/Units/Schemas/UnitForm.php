<?php

namespace App\Filament\Resources\Units\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Satuan')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama Satuan')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('code')
                            ->label('Kode')
                            ->required()
                            ->maxLength(20)
                            ->placeholder('kg, pcs, m, jam'),
                        Select::make('type')
                            ->label('Tipe')
                            ->options([
                                'weight' => 'Berat (Weight)',
                                'volume' => 'Volume',
                                'length' => 'Panjang (Length)',
                                'piece' => 'Satuan (Piece)',
                                'time' => 'Waktu (Time)',
                            ])
                            ->required()
                            ->default('piece'),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}
