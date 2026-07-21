<?php

namespace App\Filament\Resources\SalesTargets\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SalesTargetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Target')
                    ->columns(2)
                    ->schema([
                        Select::make('employee_id')
                            ->label('Karyawan')
                            ->relationship('employee', 'first_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('year')
                            ->label('Tahun')
                            ->numeric()
                            ->required()
                            ->default(now()->year),
                        TextInput::make('month')
                            ->label('Bulan')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(12)
                            ->required()
                            ->default(now()->month),
                        TextInput::make('target_amount')
                            ->label('Target (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),
                        TextInput::make('achieved_amount')
                            ->label('Tercapai (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                    ]),
            ]);
    }
}
