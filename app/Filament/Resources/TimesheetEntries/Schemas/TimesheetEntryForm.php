<?php

namespace App\Filament\Resources\TimesheetEntries\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TimesheetEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Entri Timesheet')
                    ->columns(2)
                    ->schema([
                        Select::make('timesheet_id')
                            ->label('Timesheet')
                            ->relationship('timesheet', 'id')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('task_id')
                            ->label('Tugas')
                            ->relationship('task', 'title')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        DateTimePicker::make('start_time')
                            ->label('Waktu Mulai')
                            ->required(),
                        DateTimePicker::make('end_time')
                            ->label('Waktu Selesai')
                            ->required(),
                        TextInput::make('hours')
                            ->label('Jam')
                            ->numeric()
                            ->suffix('jam'),
                        Toggle::make('is_billable')
                            ->label('Ditagihkan')
                            ->default(true),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
