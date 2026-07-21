<?php

namespace App\Filament\Resources\SignatureProviders\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SignatureProviderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Provider')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(1),
                        TextInput::make('name')
                            ->label('Nama Provider')
                            ->required()
                            ->maxLength(255)
                            ->hint('Contoh: Mekari e-Sign, Privy, Digisign'),
                        TextInput::make('base_url')
                            ->label('Base URL')
                            ->url()
                            ->maxLength(255)
                            ->hint('Contoh: https://api.mekari.com'),
                        TextInput::make('api_key')
                            ->label('API Key')
                            ->password()
                            ->maxLength(500)
                            ->dehydrated(false)
                            ->hint('API key akan dienkripsi saat disimpan'),
                        Select::make('api_format')
                            ->label('Format API')
                            ->options([
                                'rest' => 'REST API',
                                'mekari' => 'Mekari e-Sign',
                                'privy' => 'PrivyID',
                                'digisign' => 'Digisign',
                            ])
                            ->default('rest'),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(false)
                            ->hint('Hanya satu provider yang dapat aktif'),
                    ]),
                Section::make('Webhook')
                    ->schema([
                        Placeholder::make('webhook_url')
                            ->label('URL Webhook')
                            ->content(url('/api/webhooks/signature'))
                            ->hint('Gunakan URL ini untuk konfigurasi webhook di provider'),
                    ]),
            ]);
    }
}
