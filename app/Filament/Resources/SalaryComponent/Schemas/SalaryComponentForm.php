<?php

namespace App\Filament\Resources\SalaryComponent\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SalaryComponentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Komponen Gaji')
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
                            ->label('Nama Komponen')
                            ->required()
                            ->maxLength(255),
                        Select::make('type')
                            ->label('Tipe')
                            ->options([
                                'income' => 'Pendapatan',
                                'deduction' => 'Potongan',
                            ])
                            ->required(),
                        Select::make('calculation_type')
                            ->label('Tipe Perhitungan')
                            ->options([
                                'fixed' => 'Tetap',
                                'percentage' => 'Persentase',
                                'formula' => 'Formula',
                                'per_day' => 'Per Hari',
                                'per_hour' => 'Per Jam',
                                'per_attendance' => 'Per Kehadiran',
                            ])
                            ->required(),
                        TextInput::make('amount')
                            ->label('Jumlah')
                            ->numeric()
                            ->nullable()
                            ->helperText('Diisi jika tipe perhitungan = Tetap'),
                        Textarea::make('formula')
                            ->label('Formula')
                            ->rows(3)
                            ->nullable()
                            ->helperText('Diisi jika tipe perhitungan = Formula'),
                        TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0),
                    ]),
                Section::make('Pengaturan')
                    ->columns(1)
                    ->schema([
                        Toggle::make('is_taxable')
                            ->label('Kena Pajak')
                            ->default(false),
                        Toggle::make('is_mandatory')
                            ->label('Wajib')
                            ->default(false),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}