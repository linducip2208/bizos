<?php

namespace App\Filament\Resources\Holidays\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HolidayForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Hari Libur')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama Hari Libur')
                            ->required()
                            ->maxLength(255),
                        DatePicker::make('date')
                            ->label('Tanggal')
                            ->required(),
                        Select::make('type')
                            ->label('Tipe')
                            ->options([
                                'national' => 'Nasional',
                                'company' => 'Perusahaan',
                                'religious' => 'Keagamaan',
                            ])
                            ->default('company')
                            ->required(),
                        TextInput::make('year')
                            ->label('Tahun')
                            ->numeric()
                            ->nullable()
                            ->helperText('Kosongkan jika berulang setiap tahun'),
                        Toggle::make('is_recurring')
                            ->label('Berulang Setiap Tahun')
                            ->default(false),
                    ]),
            ]);
    }
}
