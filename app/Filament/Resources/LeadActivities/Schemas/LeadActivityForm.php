<?php

namespace App\Filament\Resources\LeadActivities\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class LeadActivityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Aktivitas Lead')
                    ->columns(2)
                    ->schema([
                        Select::make('lead_id')
                            ->label('Lead')
                            ->relationship('lead', 'email')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('employee_id')
                            ->label('Karyawan')
                            ->relationship('employee', 'first_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('activity_type')
                            ->label('Tipe Aktivitas')
                            ->required()
                            ->options([
                                'panggilan' => 'Panggilan',
                                'email' => 'Email',
                                'pertemuan' => 'Pertemuan',
                                'presentasi' => 'Presentasi',
                                'follow_up' => 'Follow Up',
                                'demo' => 'Demo',
                                'proposal' => 'Proposal',
                                'negosiasi' => 'Negosiasi',
                                'lainnya' => 'Lainnya',
                            ]),
                        TextInput::make('subject')
                            ->label('Subjek')
                            ->required()
                            ->maxLength(255),
                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->default('direncanakan')
                            ->options([
                                'direncanakan' => 'Direncanakan',
                                'selesai' => 'Selesai',
                                'dibatalkan' => 'Dibatalkan',
                            ]),
                        DateTimePicker::make('scheduled_at')
                            ->label('Jadwal'),
                        DateTimePicker::make('completed_at')
                            ->label('Selesai Pada'),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}