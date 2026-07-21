<?php

namespace App\Filament\Resources\SmsGatewayResource\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Schema;

class SmsGatewayForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('SMS Gateway')->columns(2)->schema([
                Select::make('company_id')->label('Perusahaan')->relationship('company','name')->searchable()->preload()->required(),
                TextInput::make('name')->label('Nama Gateway')->required()->maxLength(100),
                Select::make('provider')->label('Provider')->options(['twilio'=>'Twilio','vonage'=>'Vonage','zenziva'=>'ZenZiva','gammu'=>'Gammu'])->required(),
                TextInput::make('api_key_encrypted')->label('API Key')->password()->revealable()->maxLength(500),
                TextInput::make('api_secret_encrypted')->label('API Secret')->password()->revealable()->maxLength(500),
                TextInput::make('sender_id')->label('Sender ID')->maxLength(20),
                TextInput::make('base_url')->label('Base URL')->url()->maxLength(255),
                Toggle::make('is_active')->label('Aktif')->default(false),
                KeyValue::make('extra_config')->label('Konfigurasi Tambahan')->keyLabel('Key')->valueLabel('Value')->columnSpanFull(),
            ]),
        ]);
    }
}
