<?php

namespace App\Filament\Resources\Overtimes\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class OvertimeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Lembur')
                    ->columns(3)
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
                        TimePicker::make('start_time')
                            ->label('Jam Mulai')
                            ->required(),
                        TimePicker::make('end_time')
                            ->label('Jam Selesai')
                            ->required(),
                        TextInput::make('duration_minutes')
                            ->label('Durasi (Menit)')
                            ->numeric()
                            ->required(),
                        TextInput::make('rate_multiplier')
                            ->label('Rate Multiplier')
                            ->numeric()
                            ->default(1.5)
                            ->required(),
                        Textarea::make('reason')
                            ->label('Alasan Lembur')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                            ])
                            ->default('pending')
                            ->required(),
                    ]),
            ]);
    }
}