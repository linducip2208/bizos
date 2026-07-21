<?php

namespace App\Filament\Resources\BudgetItem\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BudgetItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Item Anggaran')
                    ->columns(2)
                    ->schema([
                        Select::make('budget_id')
                            ->label('Anggaran')
                            ->relationship('budget', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('coa_id')
                            ->label('Akun')
                            ->relationship('coa', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('description')
                            ->label('Deskripsi')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('planned_amount')
                            ->label('Jumlah Rencana')
                            ->numeric()
                            ->required()
                            ->default(0),
                        TextInput::make('actual_amount')
                            ->label('Jumlah Aktual')
                            ->numeric()
                            ->nullable()
                            ->default(0),
                        TextInput::make('variance')
                            ->label('Varians')
                            ->numeric()
                            ->nullable(),
                        DatePicker::make('period_start')
                            ->label('Periode Mulai')
                            ->nullable(),
                        DatePicker::make('period_end')
                            ->label('Periode Selesai')
                            ->nullable(),
                    ]),
            ]);
    }
}
