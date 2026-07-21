<?php

namespace App\Filament\Resources\Referrals\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ReferralForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Referral')
                    ->columns(2)
                    ->schema([
                        Select::make('referrer_client_id')
                            ->label('Klien Pereferensi')
                            ->relationship('referrerClient', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('referred_name')
                            ->label('Nama Direferensi')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('referred_phone')
                            ->label('Telepon Direferensi')
                            ->nullable()
                            ->maxLength(20),
                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->default('pending')
                            ->options([
                                'pending' => 'Pending',
                                'signed_up' => 'Mendaftar',
                                'converted' => 'Terkonversi',
                            ]),
                        Select::make('reward_status')
                            ->label('Status Reward')
                            ->required()
                            ->default('pending')
                            ->options([
                                'pending' => 'Pending',
                                'earned' => 'Didapat',
                                'paid' => 'Dibayar',
                            ]),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
