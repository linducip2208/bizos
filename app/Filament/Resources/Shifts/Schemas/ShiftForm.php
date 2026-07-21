<?php

namespace App\Filament\Resources\Shifts\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ShiftForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Shift')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama Shift')
                            ->required()
                            ->maxLength(100),
                        TimePicker::make('start_time')
                            ->label('Jam Mulai')
                            ->required(),
                        TimePicker::make('end_time')
                            ->label('Jam Selesai')
                            ->required(),
                        TextInput::make('grace_period_minutes')
                            ->label('Grace Period (Menit)')
                            ->numeric()
                            ->default(15),
                        TimePicker::make('break_start')
                            ->label('Mulai Istirahat')
                            ->nullable(),
                        TimePicker::make('break_end')
                            ->label('Selesai Istirahat')
                            ->nullable(),
                    ]),
                Section::make('Status')
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_overnight')
                            ->label('Lintas Hari')
                            ->default(false),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}
