<?php

namespace App\Filament\Resources\Grades\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class GradeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Grade')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('code')
                            ->label('Kode')
                            ->required()
                            ->maxLength(50),
                        TextInput::make('name')
                            ->label('Nama Grade')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('min_salary')
                            ->label('Gaji Minimum')
                            ->numeric()
                            ->prefix('Rp')
                            ->helperText('Gaji minimum untuk grade ini'),
                        TextInput::make('max_salary')
                            ->label('Gaji Maksimum')
                            ->numeric()
                            ->prefix('Rp')
                            ->helperText('Gaji maksimum untuk grade ini'),
                    ]),
                Section::make('Status')
                    ->columns(1)
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}