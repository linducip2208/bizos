<?php

namespace App\Filament\Resources\Calendars\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CalendarForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kalender')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama Kalender')
                            ->required()
                            ->maxLength(255),
                        ColorPicker::make('color')
                            ->label('Warna'),
                        Toggle::make('is_public')
                            ->label('Publik')
                            ->default(false),
                    ]),
            ]);
    }
}
