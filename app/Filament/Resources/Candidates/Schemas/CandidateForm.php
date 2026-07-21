<?php

namespace App\Filament\Resources\Candidates\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CandidateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Kandidat')
                    ->columns(2)
                    ->schema([
                        Select::make('job_posting_id')
                            ->label('Lowongan')
                            ->relationship('jobPosting', 'title')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('first_name')
                            ->label('Nama Depan')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('last_name')
                            ->label('Nama Belakang')
                            ->nullable()
                            ->maxLength(100),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Telepon')
                            ->tel()
                            ->nullable()
                            ->maxLength(30),
                        TextInput::make('source')
                            ->label('Sumber')
                            ->nullable()
                            ->maxLength(100),
                    ]),
                Section::make('Detail Lamaran')
                    ->columns(2)
                    ->schema([
                        TextInput::make('expected_salary')
                            ->label('Gaji Diharapkan')
                            ->numeric()
                            ->prefix('Rp')
                            ->nullable(),
                        DatePicker::make('available_date')
                            ->label('Tanggal Tersedia')
                            ->nullable(),
                        Select::make('pipeline_stage')
                            ->label('Tahapan')
                            ->options([
                                'applied' => 'Melamar',
                                'screening' => 'Screening',
                                'interview' => 'Interview',
                                'technical_test' => 'Test Teknis',
                                'offering' => 'Penawaran',
                                'hired' => 'Diterima',
                                'rejected' => 'Ditolak',
                            ])
                            ->default('applied')
                            ->required(),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}