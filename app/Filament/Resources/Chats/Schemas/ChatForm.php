<?php

namespace App\Filament\Resources\Chats\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ChatForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Obrolan')
                    ->columns(2)
                    ->schema([
                        Select::make('chat_type')
                            ->label('Tipe Obrolan')
                            ->options([
                                'personal' => 'Personal',
                                'group' => 'Grup',
                                'department' => 'Departemen',
                            ])
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),
                        Select::make('department_id')
                            ->label('Departemen')
                            ->relationship('department', 'name')
                            ->nullable()
                            ->preload()
                            ->searchable(),
                        Select::make('created_by')
                            ->label('Dibuat Oleh')
                            ->relationship('createdBy', 'name')
                            ->required()
                            ->preload()
                            ->searchable(),
                    ]),
            ]);
    }
}