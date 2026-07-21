<?php

namespace App\Filament\Resources\EcommerceChannel\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EcommerceChannelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Channel E-Commerce')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('channel_name')
                            ->label('Nama Channel')
                            ->options([
                                'shopee' => 'Shopee',
                                'tokopedia' => 'Tokopedia',
                                'bukalapak' => 'Bukalapak',
                                'tiktok_shop' => 'TikTok Shop',
                                'lazada' => 'Lazada',
                                'blibli' => 'Blibli',
                            ])
                            ->required(),
                        TextInput::make('api_key')
                            ->label('API Key')
                            ->password()
                            ->maxLength(500)
                            ->nullable(),
                        TextInput::make('api_secret')
                            ->label('API Secret')
                            ->password()
                            ->maxLength(500)
                            ->nullable(),
                        TextInput::make('shop_id')
                            ->label('Shop ID')
                            ->maxLength(100)
                            ->nullable(),
                        TextInput::make('webhook_secret')
                            ->label('Webhook Secret')
                            ->password()
                            ->maxLength(200)
                            ->nullable(),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(false),
                    ]),
            ]);
    }
}