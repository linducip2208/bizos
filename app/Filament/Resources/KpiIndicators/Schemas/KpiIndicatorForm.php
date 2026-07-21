<?php

namespace App\Filament\Resources\KpiIndicators\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class KpiIndicatorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Indikator')
                    ->columns(2)
                    ->schema([
                        Select::make('template_id')
                            ->label('Template KPI')
                            ->relationship('template', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama Indikator')
                            ->required()
                            ->maxLength(255),
                        Select::make('category')
                            ->label('Kategori BSC')
                            ->options([
                                'financial' => 'Keuangan',
                                'customer' => 'Pelanggan',
                                'internal_process' => 'Proses Internal',
                                'learning_growth' => 'Pembelajaran & Pertumbuhan',
                            ])
                            ->required(),
                        TextInput::make('weight_percent')
                            ->label('Bobot (%)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),
                        Select::make('target_type')
                            ->label('Tipe Target')
                            ->options([
                                'numeric' => 'Numerik',
                                'percentage' => 'Persentase',
                                'boolean' => 'Ya/Tidak',
                                'rating_1_5' => 'Rating 1-5',
                            ])
                            ->required(),
                        TextInput::make('target_value')
                            ->label('Nilai Target')
                            ->numeric()
                            ->nullable(),
                        TextInput::make('measurement_unit')
                            ->label('Satuan')
                            ->maxLength(50),
                        TextInput::make('data_source')
                            ->label('Sumber Data')
                            ->maxLength(255),
                        TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}