<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Langganan')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('plan_id')
                            ->label('Paket')
                            ->relationship('plan', 'name', fn ($query) => $query->active()->orderBy('sort_order'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'trial' => 'Trial',
                                'active' => 'Aktif',
                                'grace' => 'Grace Period',
                                'expired' => 'Kedaluwarsa',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->required()
                            ->default('trial'),
                        Toggle::make('auto_renew')
                            ->label('Perpanjang Otomatis')
                            ->default(true),
                    ]),
                Section::make('Periode')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('started_at')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->default(now()),
                        DatePicker::make('ends_at')
                            ->label('Tanggal Berakhir')
                            ->nullable(),
                        DatePicker::make('trial_ends_at')
                            ->label('Trial Berakhir')
                            ->nullable(),
                        DatePicker::make('cancelled_at')
                            ->label('Tanggal Dibatalkan')
                            ->nullable()
                            ->disabled(),
                    ]),
            ]);
    }
}