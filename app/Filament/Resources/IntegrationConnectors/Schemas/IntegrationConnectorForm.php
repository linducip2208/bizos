<?php

namespace App\Filament\Resources\IntegrationConnectors\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class IntegrationConnectorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Konfigurasi Konektor')
                    ->columns(3)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('connector_type')
                            ->label('Jenis Konektor')
                            ->options([
                                'jurnal_id' => 'Jurnal.id',
                                'xero' => 'Xero',
                                'accurate' => 'Accurate Online',
                                'google_workspace' => 'Google Workspace',
                                'microsoft_365' => 'Microsoft 365',
                                'open_banking' => 'Open Banking',
                                'djp' => 'DJP (Pajak)',
                            ])
                            ->required()
                            ->live(),
                        TextInput::make('name')
                            ->label('Nama Koneksi')
                            ->required()
                            ->maxLength(200),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'disconnected' => 'Terputus',
                                'connecting' => 'Menghubungkan',
                                'connected' => 'Terhubung',
                                'error' => 'Error',
                            ])
                            ->default('disconnected')
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                        Textarea::make('configuration')
                            ->label('Konfigurasi (JSON)')
                            ->rows(6)
                            ->nullable()
                            ->columnSpanFull()
                            ->formatStateUsing(fn($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state),
                        Textarea::make('last_error_message')
                            ->label('Pesan Error Terakhir')
                            ->rows(2)
                            ->disabled()
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}