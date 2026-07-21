<?php

namespace App\Filament\Resources\Integrations\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class IntegrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Integrasi')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),
                        Select::make('integration_type')
                            ->label('Tipe Integrasi')
                            ->options([
                                'payment' => 'Payment Gateway',
                                'sms' => 'SMS Gateway',
                                'email' => 'Email Provider',
                                'storage' => 'Storage Provider',
                                'webhook' => 'Webhook',
                                'oauth' => 'OAuth Provider',
                                'push' => 'Push Notification',
                            ])
                            ->required(),
                        TextInput::make('api_format')
                            ->label('Format API')
                            ->maxLength(255),
                        TextInput::make('base_url')
                            ->label('Base URL')
                            ->url()
                            ->maxLength(255),
                        TextInput::make('api_key_encrypted')
                            ->label('API Key')
                            ->password()
                            ->maxLength(255),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}