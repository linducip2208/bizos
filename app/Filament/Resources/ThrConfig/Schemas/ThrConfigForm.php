<?php

namespace App\Filament\Resources\ThrConfig\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ThrConfigForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Konfigurasi THR')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('religious_holiday')
                            ->label('Hari Raya')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('min_months_service')
                            ->label('Minimal Masa Kerja (Bulan)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->default(3),
                        Select::make('formula')
                            ->label('Formula')
                            ->options([
                                'full_basic_salary' => 'Gaji Pokok Penuh',
                                'proportional' => 'Proporsional Masa Kerja',
                                'custom' => 'Kustom',
                            ])
                            ->required()
                            ->default('full_basic_salary'),
                        Textarea::make('custom_formula')
                            ->label('Formula Kustom')
                            ->nullable()
                            ->columnSpanFull(),
                        TextInput::make('payment_deadline_days')
                            ->label('Batas Pembayaran (H- Sebelum Hari Raya)')
                            ->numeric()
                            ->required()
                            ->default(7),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}