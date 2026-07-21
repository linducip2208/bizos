<?php

namespace App\Filament\Resources\PayrollItem\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PayrollItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Item Penggajian')
                    ->columns(2)
                    ->schema([
                        Select::make('payroll_id')
                            ->label('Penggajian')
                            ->relationship('payroll', 'id')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('salary_component_id')
                            ->label('Komponen Gaji')
                            ->relationship('salaryComponent', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('name')
                            ->label('Nama Item')
                            ->required()
                            ->maxLength(255),
                        Select::make('type')
                            ->label('Tipe')
                            ->options([
                                'income' => 'Pendapatan',
                                'deduction' => 'Potongan',
                            ])
                            ->required(),
                        TextInput::make('amount')
                            ->label('Jumlah')
                            ->numeric()
                            ->required(),
                    ]),
            ]);
    }
}