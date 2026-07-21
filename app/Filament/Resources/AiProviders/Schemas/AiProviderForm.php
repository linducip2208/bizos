<?php

namespace App\Filament\Resources\AiProviders\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AiProviderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Provider AI')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama Provider')
                            ->required()
                            ->maxLength(255),
                        Select::make('api_format')
                            ->label('Format API')
                            ->options([
                                'openai' => 'OpenAI Compatible',
                                'anthropic' => 'Anthropic',
                                'gemini' => 'Gemini',
                            ])
                            ->required(),
                        TextInput::make('base_url')
                            ->label('Base URL')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('api_key_encrypted')
                            ->label('API Key')
                            ->password()
                            ->maxLength(255),
                        TextInput::make('default_model')
                            ->label('Default Model')
                            ->maxLength(255),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}