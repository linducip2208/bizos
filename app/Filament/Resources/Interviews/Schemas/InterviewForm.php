<?php

namespace App\Filament\Resources\Interviews\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class InterviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Interview')
                    ->columns(3)
                    ->schema([
                        Select::make('candidate_id')
                            ->label('Kandidat')
                            ->relationship('candidate', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => trim($record->first_name . ' ' . $record->last_name))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('interview_type')
                            ->label('Tipe Interview')
                            ->options([
                                'phone' => 'Telepon',
                                'video' => 'Video',
                                'onsite' => 'Onsite',
                                'technical_test' => 'Tes Teknis',
                                'final' => 'Final',
                            ])
                            ->required(),
                        DateTimePicker::make('scheduled_at')
                            ->label('Jadwal')
                            ->required(),
                        TextInput::make('duration_minutes')
                            ->label('Durasi (Menit)')
                            ->numeric()
                            ->nullable(),
                        TextInput::make('location')
                            ->label('Lokasi')
                            ->maxLength(255)
                            ->nullable(),
                        TextInput::make('meeting_link')
                            ->label('Link Meeting')
                            ->url()
                            ->maxLength(255)
                            ->nullable(),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'scheduled' => 'Terjadwal',
                                'in_progress' => 'Sedang Berlangsung',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                                'no_show' => 'Tidak Hadir',
                            ])
                            ->default('scheduled')
                            ->required(),
                    ]),
            ]);
    }
}