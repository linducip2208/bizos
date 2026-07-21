<?php

namespace App\Filament\Resources\CourseEnrollments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CourseEnrollmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Pendaftaran')
                    ->columns(2)
                    ->schema([
                        Select::make('course_id')
                            ->label('Kursus')
                            ->relationship('course', 'title')
                            ->required()
                            ->preload()
                            ->searchable(),
                        Select::make('employee_id')
                            ->label('Karyawan')
                            ->relationship('employee', 'name')
                            ->required()
                            ->preload()
                            ->searchable(),
                        DateTimePicker::make('enrolled_at')
                            ->label('Tanggal Daftar')
                            ->default(now()),
                        DateTimePicker::make('started_at')
                            ->label('Tanggal Mulai'),
                        DateTimePicker::make('completed_at')
                            ->label('Tanggal Selesai'),
                        TextInput::make('progress_percent')
                            ->label('Progress (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'enrolled' => 'Terdaftar',
                                'in_progress' => 'Sedang Belajar',
                                'completed' => 'Selesai',
                                'dropped' => 'Drop Out',
                            ])
                            ->required()
                            ->default('enrolled'),
                        Toggle::make('certificate_issued')
                            ->label('Sertifikat Diterbitkan'),
                    ]),
            ]);
    }
}