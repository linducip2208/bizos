<?php

namespace App\Filament\Resources\CalendarEvents\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CalendarEventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Acara')
                    ->columns(2)
                    ->schema([
                        Select::make('calendar_id')
                            ->label('Kalender')
                            ->relationship('calendar', 'name')
                            ->required()
                            ->preload()
                            ->searchable(),
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->columnSpanFull(),
                        DateTimePicker::make('start_time')
                            ->label('Waktu Mulai')
                            ->required(),
                        DateTimePicker::make('end_time')
                            ->label('Waktu Selesai')
                            ->required(),
                        Toggle::make('is_all_day')
                            ->label('Sepanjang Hari'),
                        TextInput::make('location')
                            ->label('Lokasi')
                            ->maxLength(255),
                        ColorPicker::make('color')
                            ->label('Warna'),
                    ]),
            ]);
    }
}
