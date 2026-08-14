<?php

namespace App\Filament\Resources\Scenarios\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ScenarioForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Skenario')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama Skenario')
                            ->required()
                            ->maxLength(255),
                        Select::make('scenario_type')
                            ->label('Tipe Skenario')
                            ->options([
                                'best_case' => 'Best Case',
                                'base_case' => 'Base Case',
                                'worst_case' => 'Worst Case',
                                'custom' => 'Custom',
                            ])
                            ->required()
                            ->default('base_case'),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull()
                            ->rows(3),
                        Textarea::make('assumptions')
                            ->label('Asumsi (JSON)')
                            ->columnSpanFull()
                            ->rows(4)
                            ->helperText('Format JSON: {"asumsi_1": "nilai", "asumsi_2": "nilai"}'),
                        Select::make('parent_budget_id')
                            ->label('Anggaran Induk')
                            ->relationship('parentBudget', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(false),
                        Select::make('created_by')
                            ->label('Dibuat Oleh')
                            ->relationship('createdBy', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
            ]);
    }
}
