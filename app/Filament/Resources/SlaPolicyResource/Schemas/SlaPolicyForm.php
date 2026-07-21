<?php

namespace App\Filament\Resources\SlaPolicyResource\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SlaPolicyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kebijakan SLA')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Kebijakan')
                            ->required()
                            ->maxLength(255),
                        Select::make('category_id')
                            ->label('Kategori Tiket')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('priority')
                            ->label('Prioritas')
                            ->required()
                            ->options([
                                'low' => 'Rendah',
                                'medium' => 'Sedang',
                                'high' => 'Tinggi',
                                'urgent' => 'Urgent',
                            ])
                            ->default('medium'),
                        TextInput::make('response_time_hours')
                            ->label('Waktu Respons (Jam)')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(8),
                        TextInput::make('resolution_time_hours')
                            ->label('Waktu Penyelesaian (Jam)')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(24),
                        Toggle::make('business_hours_only')
                            ->label('Hanya Jam Kerja')
                            ->default(true),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}
