<?php

namespace App\Filament\Resources\PerformanceCycles\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PerformanceCycleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Siklus')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama Siklus')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Q1 2026, Annual Review 2026'),
                        DatePicker::make('period_start')
                            ->label('Periode Mulai')
                            ->required(),
                        DatePicker::make('period_end')
                            ->label('Periode Selesai')
                            ->required()
                            ->after('period_start'),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'active' => 'Aktif',
                                'review' => 'Review',
                                'completed' => 'Selesai',
                            ])
                            ->default('draft')
                            ->required(),
                    ]),
            ]);
    }
}
