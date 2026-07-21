<?php

namespace App\Filament\Resources\BpjsConfig\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BpjsConfigForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Konfigurasi BPJS')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('bpjs_type')
                            ->label('Tipe BPJS')
                            ->options([
                                'jht' => 'JHT - Jaminan Hari Tua',
                                'jp' => 'JP - Jaminan Pensiun',
                                'jkk' => 'JKK - Jaminan Kecelakaan Kerja',
                                'jkm' => 'JKM - Jaminan Kematian',
                                'kes' => 'Kesehatan',
                            ])
                            ->required(),
                        TextInput::make('company_rate')
                            ->label('Tarif Perusahaan (%)')
                            ->numeric()
                            ->inputMode('decimal')
                            ->required(),
                        TextInput::make('employee_rate')
                            ->label('Tarif Karyawan (%)')
                            ->numeric()
                            ->inputMode('decimal')
                            ->required(),
                        TextInput::make('max_salary_cap')
                            ->label('Batas Gaji Maksimal (Rp)')
                            ->numeric()
                            ->required(),
                        TextInput::make('effective_year')
                            ->label('Tahun Berlaku')
                            ->numeric()
                            ->required()
                            ->minValue(2000)
                            ->maxValue(2100),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}