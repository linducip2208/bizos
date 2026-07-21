<?php

namespace App\Filament\Resources\TicketTagResource\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TicketTagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Label')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Label')
                            ->required()
                            ->maxLength(100),
                        ColorPicker::make('color')
                            ->label('Warna')
                            ->default('#6366f1'),
                    ]),
            ]);
    }
}
