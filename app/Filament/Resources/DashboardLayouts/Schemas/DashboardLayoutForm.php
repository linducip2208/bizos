<?php

namespace App\Filament\Resources\DashboardLayouts\Schemas;

use Filament\Schemas\Components\Section as FormSection;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DashboardLayoutForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FormSection::make('Informasi Layout')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Layout')
                            ->required()
                            ->maxLength(255),
                        Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Toggle::make('is_default')
                            ->label('Default')
                            ->default(false),
                    ]),
                FormSection::make('Konfigurasi Grid')
                    ->schema([
                        TextInput::make('layout_config.columns')
                            ->label('Jumlah Kolom')
                            ->numeric()
                            ->default(12)
                            ->minValue(1)
                            ->maxValue(24),
                        TextInput::make('layout_config.row_height')
                            ->label('Tinggi Baris (px)')
                            ->numeric()
                            ->default(120)
                            ->minValue(60)
                            ->maxValue(500),
                        Select::make('layout_config.compact_type')
                            ->label('Tipe Compact')
                            ->options([
                                'vertical' => 'Vertical',
                                'horizontal' => 'Horizontal',
                            ])
                            ->default('vertical'),
                    ]),
            ]);
    }
}