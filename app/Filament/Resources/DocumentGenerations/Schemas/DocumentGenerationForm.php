<?php

namespace App\Filament\Resources\DocumentGenerations\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DocumentGenerationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Dokumen')
                    ->columns(2)
                    ->schema([
                        TextInput::make('module')
                            ->label('Module')
                            ->disabled(),
                        TextInput::make('module_id')
                            ->label('Module ID')
                            ->disabled(),
                        TextInput::make('status')
                            ->label('Status')
                            ->disabled(),
                    ]),
            ]);
    }
}