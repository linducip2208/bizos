<?php

namespace App\Filament\Resources\PaymentGatewayConfigs\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentGatewayConfigForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Gateway')
                    ->description('Semua kunci API dienkripsi saat disimpan. Kredensial diisi sendiri oleh user (tidak ada provider yang di-hardcode).')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama Gateway')
                            ->required()
                            ->maxLength(100),
                        Select::make('gateway_type')
                            ->label('Tipe Gateway')
                            ->options([
                                'midtrans' => 'Midtrans',
                                'xendit' => 'Xendit',
                                'stripe' => 'Stripe',
                                'custom' => 'Custom',
                            ])
                            ->required()
                            ->live(),
                        TextInput::make('base_url')
                            ->label('Base URL')
                            ->url()
                            ->maxLength(255)
                            ->helperText('Kosongkan untuk pakai URL bawaan gateway.'),
                    ]),
                Section::make('Kredensial API')
                    ->description('Isi sesuai gateway yang dipilih. Server Key & Client Key untuk Midtrans, API Key untuk Xendit/Stripe.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('api_key')
                            ->label('API Key')
                            ->password()
                            ->revealable()
                            ->maxLength(500)
                            ->helperText('Xendit / Stripe / Custom'),
                        TextInput::make('api_secret')
                            ->label('API Secret')
                            ->password()
                            ->revealable()
                            ->maxLength(500)
                            ->helperText('Opsional (webhook secret / signature key)'),
                        TextInput::make('server_key')
                            ->label('Server Key')
                            ->password()
                            ->revealable()
                            ->maxLength(500)
                            ->helperText('Midtrans'),
                        TextInput::make('client_key')
                            ->label('Client Key')
                            ->password()
                            ->revealable()
                            ->maxLength(500)
                            ->helperText('Midtrans'),
                    ]),
                Section::make('Pengaturan')
                    ->columns(1)
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                        KeyValue::make('extra_config')
                            ->label('Konfigurasi Tambahan')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->helperText('Contoh: webhook_secret, signature_key, callback_token, dsb.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
