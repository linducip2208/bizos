<?php

namespace App\Filament\Resources\AiConversations\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AiConversationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Percakapan AI')
                    ->columns(2)
                    ->schema([
                        Select::make('employee_id')
                            ->label('Karyawan')
                            ->relationship('employee', 'name')
                            ->required()
                            ->preload()
                            ->searchable(),
                        Select::make('provider_id')
                            ->label('Provider AI')
                            ->relationship('provider', 'name')
                            ->required()
                            ->preload()
                            ->searchable(),
                        TextInput::make('title')
                            ->label('Judul')
                            ->maxLength(255),
                        TextInput::make('model')
                            ->label('Model')
                            ->maxLength(100)
                            ->helperText('Contoh: gpt-4o, claude-3-5-sonnet'),
                        TextInput::make('context_type')
                            ->label('Tipe Konteks')
                            ->maxLength(50),
                    ]),
            ]);
    }
}