<?php

namespace App\Filament\Resources\EmploymentTypes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmploymentTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Tipe Karyawan')
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
                            ->label('Nama Tipe')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Contoh: Tetap, Kontrak, Magang, Freelance, Percobaan'),
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
