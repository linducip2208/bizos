<?php

namespace App\Filament\Resources\CashDenominations\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CashDenominationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pecahan')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('branch_id')
                            ->label('Cabang')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('label')
                            ->label('Label')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('cth: Rp 100.000'),
                        TextInput::make('value')
                            ->label('Nilai (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                        TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->integer()
                            ->default(0),
                    ]),
                Section::make('Status')
                    ->columns(1)
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}
