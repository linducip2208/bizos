<?php

namespace App\Filament\Resources\WorkCenterResource\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WorkCenterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Work Center')
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
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),
                        Select::make('type')
                            ->label('Tipe')
                            ->options([
                                'machine' => 'Mesin (Machine)',
                                'manual' => 'Manual',
                                'assembly' => 'Perakitan (Assembly)',
                            ])
                            ->required()
                            ->default('manual'),
                        TextInput::make('capacity_per_day')
                            ->label('Kapasitas per Hari')
                            ->numeric()
                            ->inputMode('decimal')
                            ->required()
                            ->default(8),
                        TextInput::make('capacity_uom')
                            ->label('Satuan Kapasitas')
                            ->default('unit')
                            ->maxLength(20),
                        TextInput::make('hourly_cost')
                            ->label('Biaya per Jam (Rp)')
                            ->numeric()
                            ->inputMode('decimal')
                            ->prefix('Rp')
                            ->default(0),
                        TextInput::make('overhead_rate_percent')
                            ->label('Overhead Rate (%)')
                            ->numeric()
                            ->inputMode('decimal')
                            ->suffix('%')
                            ->default(0),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}
