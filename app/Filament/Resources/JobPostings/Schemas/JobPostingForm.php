<?php

namespace App\Filament\Resources\JobPostings\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class JobPostingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Lowongan')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255),
                        Select::make('department_id')
                            ->label('Departemen')
                            ->relationship('department', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('position_id')
                            ->label('Posisi')
                            ->relationship('position', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('location')
                            ->label('Lokasi')
                            ->maxLength(255)
                            ->nullable(),
                        TextInput::make('quota')
                            ->label('Kuota')
                            ->numeric()
                            ->default(1),
                        Select::make('employee_type')
                            ->label('Tipe Karyawan')
                            ->options([
                                'permanent' => 'Tetap',
                                'contract' => 'Kontrak',
                                'probation' => 'Probation',
                                'intern' => 'Magang',
                                'freelance' => 'Freelance',
                            ])
                            ->nullable(),
                        Toggle::make('is_remote')
                            ->label('Remote')
                            ->default(false),
                    ]),
                Section::make('Gaji')
                    ->columns(2)
                    ->schema([
                        TextInput::make('min_salary')
                            ->label('Gaji Minimal')
                            ->numeric()
                            ->prefix('Rp')
                            ->nullable(),
                        TextInput::make('max_salary')
                            ->label('Gaji Maksimal')
                            ->numeric()
                            ->prefix('Rp')
                            ->nullable(),
                    ]),
                Section::make('Deskripsi')
                    ->columns(1)
                    ->schema([
                        Textarea::make('description')
                            ->label('Deskripsi Pekerjaan')
                            ->rows(5)
                            ->required()
                            ->columnSpanFull(),
                        Textarea::make('requirements')
                            ->label('Persyaratan')
                            ->rows(5)
                            ->nullable()
                            ->columnSpanFull(),
                        Textarea::make('responsibilities')
                            ->label('Tanggung Jawab')
                            ->rows(5)
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
                Section::make('Publikasi')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Dipublikasi',
                                'closed' => 'Ditutup',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->default('draft')
                            ->required(),
                        DateTimePicker::make('published_at')
                            ->label('Tanggal Publikasi')
                            ->nullable(),
                        DateTimePicker::make('closed_at')
                            ->label('Tanggal Tutup')
                            ->nullable(),
                    ]),
            ]);
    }
}
