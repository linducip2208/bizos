<?php

namespace App\Filament\Resources\Divisions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DivisionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Divisi')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('business_unit_id')
                            ->label('Unit Bisnis')
                            ->relationship('businessUnit', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('code')
                            ->label('Kode')
                            ->required()
                            ->maxLength(50),
                        TextInput::make('name')
                            ->label('Nama Divisi')
                            ->required()
                            ->maxLength(255),
                        Select::make('manager_id')
                            ->label('Manajer')
                            ->relationship('manager', 'first_name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->first_name . ' ' . $record->last_name),
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
