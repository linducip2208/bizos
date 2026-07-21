<?php

namespace App\Filament\Resources\Bonus\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BonusForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Bonus')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama Bonus')
                            ->required()
                            ->maxLength(255),
                        Select::make('type')
                            ->label('Tipe Bonus')
                            ->options([
                                'performance' => 'Kinerja',
                                'retention' => 'Retensi',
                                'holiday' => 'Hari Raya',
                                'project' => 'Proyek',
                            ])
                            ->required(),
                        Select::make('calculation_type')
                            ->label('Tipe Perhitungan')
                            ->options([
                                'fixed' => 'Tetap',
                                'percentage' => 'Persentase',
                                'manual' => 'Manual',
                            ])
                            ->required(),
                        TextInput::make('amount')
                            ->label('Jumlah')
                            ->numeric()
                            ->default(0),
                    ]),
                Section::make('Pengaturan')
                    ->columns(1)
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}
