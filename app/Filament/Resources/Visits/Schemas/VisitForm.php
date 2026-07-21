<?php

namespace App\Filament\Resources\Visits\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class VisitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Kunjungan')
                    ->columns(2)
                    ->schema([
                        Select::make('employee_id')
                            ->label('Karyawan')
                            ->relationship('employee', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => trim($record->first_name . ' ' . $record->last_name))
                            ->searchable()
                            ->preload()
                            ->required(),
                        DatePicker::make('date')
                            ->label('Tanggal')
                            ->required(),
                        Select::make('visit_type')
                            ->label('Tipe Kunjungan')
                            ->options([
                                'sales' => 'Sales',
                                'maintenance' => 'Maintenance',
                                'inspection' => 'Inspeksi',
                                'survey' => 'Survey',
                                'training' => 'Training',
                                'other' => 'Lainnya',
                            ])
                            ->required(),
                        TextInput::make('location')
                            ->label('Lokasi')
                            ->required()
                            ->maxLength(255),
                        DateTimePicker::make('start_time')
                            ->label('Jam Mulai')
                            ->required(),
                        DateTimePicker::make('end_time')
                            ->label('Jam Selesai')
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'planned' => 'Direncanakan',
                                'in_progress' => 'Sedang Berlangsung',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->default('planned')
                            ->required(),
                        Textarea::make('purpose')
                            ->label('Tujuan')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}