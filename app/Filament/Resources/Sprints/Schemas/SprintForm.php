<?php

namespace App\Filament\Resources\Sprints\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SprintForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Sprint')
                    ->columns(3)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Sprint')
                            ->required()
                            ->maxLength(255),
                        Select::make('project_id')
                            ->label('Proyek')
                            ->relationship('project', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Textarea::make('goal')
                            ->label('Tujuan Sprint')
                            ->rows(3)
                            ->maxLength(1000),
                        DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->required(),
                        DatePicker::make('end_date')
                            ->label('Tanggal Selesai')
                            ->required()
                            ->afterOrEqual('start_date'),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'planning' => 'Perencanaan',
                                'active' => 'Aktif',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->required()
                            ->default('planning'),
                        TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->integer()
                            ->default(0),
                    ]),
            ]);
    }
}