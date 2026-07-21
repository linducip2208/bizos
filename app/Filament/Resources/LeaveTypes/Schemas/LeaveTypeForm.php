<?php

namespace App\Filament\Resources\LeaveTypes\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LeaveTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Tipe Cuti')
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
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
                Section::make('Kuota')
                    ->columns(2)
                    ->schema([
                        TextInput::make('default_days')
                            ->label('Hari Default')
                            ->numeric()
                            ->required(),
                        TextInput::make('max_days')
                            ->label('Maksimal Hari')
                            ->numeric()
                            ->nullable(),
                    ]),
                Section::make('Pengaturan')
                    ->columns(2)
                    ->schema([
                        Toggle::make('is_annual')
                            ->label('Cuti Tahunan')
                            ->default(true),
                        Toggle::make('is_paid')
                            ->label('Dibayar')
                            ->default(true),
                        Toggle::make('require_attachment')
                            ->label('Wajib Lampiran')
                            ->default(false),
                        Toggle::make('require_approval')
                            ->label('Wajib Approval')
                            ->default(true),
                        TextInput::make('min_approval_level')
                            ->label('Minimal Level Approval')
                            ->numeric()
                            ->default(1),
                        ColorPicker::make('color')
                            ->label('Warna')
                            ->nullable(),
                    ]),
                Section::make('Kriteria Karyawan')
                    ->columns(2)
                    ->schema([
                        Select::make('applicable_gender')
                            ->label('Jenis Kelamin')
                            ->options([
                                'all' => 'Semua',
                                'male' => 'Laki-laki',
                                'female' => 'Perempuan',
                            ])
                            ->default('all')
                            ->required(),
                        Select::make('applicable_marital')
                            ->label('Status Pernikahan')
                            ->options([
                                'all' => 'Semua',
                                'single' => 'Belum Menikah',
                                'married' => 'Menikah',
                            ])
                            ->default('all')
                            ->required(),
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