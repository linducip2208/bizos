<?php

namespace App\Filament\Resources\WorkCalendars\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WorkCalendarForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kalender Kerja')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama Kalender')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('year')
                            ->label('Tahun')
                            ->numeric()
                            ->required()
                            ->default(now()->year),
                        Toggle::make('is_default')
                            ->label('Default')
                            ->default(false),
                    ]),
                Section::make('Konfigurasi Hari & Jam Kerja')
                    ->columns(2)
                    ->schema([
                        CheckboxList::make('config.working_days')
                            ->label('Hari Kerja')
                            ->options([
                                'senin' => 'Senin',
                                'selasa' => 'Selasa',
                                'rabu' => 'Rabu',
                                'kamis' => 'Kamis',
                                'jumat' => 'Jumat',
                                'sabtu' => 'Sabtu',
                                'minggu' => 'Minggu',
                            ])
                            ->default(['senin', 'selasa', 'rabu', 'kamis', 'jumat'])
                            ->columns(4)
                            ->columnSpanFull(),
                        TimePicker::make('config.working_hours.start')
                            ->label('Jam Masuk')
                            ->default('08:00'),
                        TimePicker::make('config.working_hours.end')
                            ->label('Jam Pulang')
                            ->default('17:00'),
                        TimePicker::make('config.break_time.start')
                            ->label('Jam Istirahat Mulai')
                            ->default('12:00'),
                        TimePicker::make('config.break_time.end')
                            ->label('Jam Istirahat Selesai')
                            ->default('13:00'),
                    ]),
            ]);
    }
}
