<?php

namespace App\Filament\Resources\ReportSchedules\Schemas;

use Filament\Schemas\Components\Section as FormSection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ReportScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FormSection::make('Informasi Jadwal')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Jadwal')
                            ->required()
                            ->maxLength(255),
                        Select::make('report_template_id')
                            ->label('Template Laporan')
                            ->relationship('reportTemplate', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('frequency')
                            ->label('Frekuensi')
                            ->required()
                            ->default('daily')
                            ->options([
                                'daily' => 'Harian',
                                'weekly' => 'Mingguan',
                                'monthly' => 'Bulanan',
                            ])
                            ->live(),
                        TimePicker::make('time_of_day')
                            ->label('Waktu Kirim')
                            ->default('08:00')
                            ->required(),
                        Select::make('day_of_week')
                            ->label('Hari (Mingguan)')
                            ->options([
                                0 => 'Minggu',
                                1 => 'Senin',
                                2 => 'Selasa',
                                3 => 'Rabu',
                                4 => 'Kamis',
                                5 => 'Jumat',
                                6 => 'Sabtu',
                            ])
                            ->visible(fn($get) => $get('frequency') === 'weekly'),
                        TextInput::make('day_of_month')
                            ->label('Tanggal (Bulanan)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(31)
                            ->visible(fn($get) => $get('frequency') === 'monthly'),
                        Select::make('format')
                            ->label('Format')
                            ->required()
                            ->default('pdf')
                            ->options([
                                'pdf' => 'PDF',
                                'excel' => 'Excel',
                                'csv' => 'CSV',
                            ]),
                    ]),

                FormSection::make('Penerima')
                    ->schema([
                        TagsInput::make('recipients')
                            ->label('Email Penerima')
                            ->helperText('Pisahkan dengan koma atau Enter')
                            ->placeholder('email@contoh.com')
                            ->splitKeys([',', 'Tab', 'Enter'])
                            ->separator(',')
                            ->columnSpanFull(),
                    ]),

                FormSection::make('Status')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}