<?php

namespace App\Filament\Resources\Meetings\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MeetingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Rapat')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('organized_by')
                            ->label('Penyelenggara')
                            ->relationship('organizer', 'first_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(500),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(4)
                            ->columnSpanFull(),
                        DateTimePicker::make('start_time')
                            ->label('Waktu Mulai')
                            ->required(),
                        DateTimePicker::make('end_time')
                            ->label('Waktu Selesai')
                            ->required(),
                        TextInput::make('location')
                            ->label('Lokasi')
                            ->maxLength(255),
                        TextInput::make('meeting_link')
                            ->label('Link Meeting')
                            ->url()
                            ->maxLength(255),
                        Select::make('meeting_type')
                            ->label('Tipe Meeting')
                            ->options([
                                'online' => 'Online',
                                'onsite' => 'Onsite',
                                'hybrid' => 'Hybrid',
                            ])
                            ->default('online')
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'scheduled' => 'Terjadwal',
                                'in_progress' => 'Sedang Berlangsung',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->default('scheduled')
                            ->required(),
                    ]),
            ]);
    }
}
