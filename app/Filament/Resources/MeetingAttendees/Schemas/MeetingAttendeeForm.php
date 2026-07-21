<?php

namespace App\Filament\Resources\MeetingAttendees\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class MeetingAttendeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Peserta Rapat')
                    ->columns(2)
                    ->schema([
                        Select::make('meeting_id')
                            ->label('Rapat')
                            ->relationship('meeting', 'title')
                            ->required()
                            ->preload()
                            ->searchable(),
                        Select::make('employee_id')
                            ->label('Karyawan')
                            ->relationship('employee', 'name')
                            ->required()
                            ->preload()
                            ->searchable(),
                        Select::make('response')
                            ->label('Respons')
                            ->options([
                                'accepted' => 'Hadir',
                                'declined' => 'Tidak Hadir',
                                'tentative' => 'Tentatif',
                                'no_response' => 'Belum Respons',
                            ])
                            ->required(),
                        DateTimePicker::make('attended_at')
                            ->label('Waktu Hadir'),
                        DateTimePicker::make('left_at')
                            ->label('Waktu Keluar'),
                    ]),
            ]);
    }
}