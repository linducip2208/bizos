<?php

namespace App\Filament\Resources\Branches\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Cabang')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('code')
                            ->label('Kode')
                            ->required()
                            ->maxLength(50),
                        TextInput::make('name')
                            ->label('Nama Cabang')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Telepon')
                            ->tel()
                            ->maxLength(30),
                        Select::make('timezone')
                            ->label('Zona Waktu')
                            ->options([
                                'Asia/Jakarta' => 'WIB - Asia/Jakarta',
                                'Asia/Makassar' => 'WITA - Asia/Makassar',
                                'Asia/Jayapura' => 'WIT - Asia/Jayapura',
                                'Asia/Singapore' => 'Asia/Singapore',
                                'Asia/Kuala_Lumpur' => 'Asia/Kuala Lumpur',
                                'Asia/Bangkok' => 'Asia/Bangkok',
                                'Asia/Manila' => 'Asia/Manila',
                                'Asia/Tokyo' => 'Asia/Tokyo',
                                'Asia/Seoul' => 'Asia/Seoul',
                                'Asia/Shanghai' => 'Asia/Shanghai',
                                'Asia/Dubai' => 'Asia/Dubai',
                                'Europe/London' => 'Europe/London',
                                'Europe/Paris' => 'Europe/Paris',
                                'Europe/Berlin' => 'Europe/Berlin',
                                'America/New_York' => 'America/New York',
                                'America/Chicago' => 'America/Chicago',
                                'America/Los_Angeles' => 'America/Los Angeles',
                                'Australia/Sydney' => 'Australia/Sydney',
                                'Pacific/Auckland' => 'Pacific/Auckland',
                                'UTC' => 'UTC',
                            ])
                            ->searchable()
                            ->default('Asia/Jakarta'),
                        Textarea::make('address')
                            ->label('Alamat')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Section::make('Status')
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_headquarters')
                            ->label('Kantor Pusat')
                            ->default(false)
                            ->helperText('Hanya satu cabang yang boleh menjadi kantor pusat'),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}