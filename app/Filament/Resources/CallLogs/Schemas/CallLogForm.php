<?php

namespace App\Filament\Resources\CallLogs\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CallLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Panggilan')
                    ->columns(2)
                    ->schema([
                        Select::make('employee_id')
                            ->label('Karyawan')
                            ->relationship('employee', 'first_name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('lead_id')
                            ->label('Lead')
                            ->relationship('lead', 'email')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('client_id')
                            ->label('Klien')
                            ->relationship('client', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('phone_number')
                            ->label('No. Telepon')
                            ->required()
                            ->maxLength(20),
                        Select::make('direction')
                            ->label('Arah')
                            ->required()
                            ->default('outbound')
                            ->options([
                                'inbound' => 'Masuk',
                                'outbound' => 'Keluar',
                            ]),
                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->default('planned')
                            ->options([
                                'planned' => 'Direncanakan',
                                'completed' => 'Selesai',
                                'missed' => 'Tidak Terjawab',
                                'voicemail' => 'Pesan Suara',
                            ]),
                        TextInput::make('duration_seconds')
                            ->label('Durasi (detik)')
                            ->numeric()
                            ->nullable(),
                        DateTimePicker::make('scheduled_at')
                            ->label('Dijadwalkan')
                            ->nullable(),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
