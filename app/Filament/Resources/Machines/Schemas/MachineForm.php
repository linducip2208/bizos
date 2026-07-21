<?php

namespace App\Filament\Resources\Machines\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MachineForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Mesin')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('work_center_id')
                            ->label('Work Center')
                            ->relationship('workCenter', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('name')
                            ->label('Nama Mesin')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('model')
                            ->label('Model')
                            ->maxLength(255),
                        TextInput::make('serial_number')
                            ->label('Nomor Seri Mesin')
                            ->maxLength(100),
                        TextInput::make('capacity_per_hour')
                            ->label('Kapasitas per Jam')
                            ->numeric()
                            ->inputMode('decimal')
                            ->required()
                            ->default(0),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Aktif',
                                'maintenance' => 'Dalam Perawatan',
                                'broken' => 'Rusak',
                            ])
                            ->required()
                            ->default('active'),
                    ]),
            ]);
    }
}
