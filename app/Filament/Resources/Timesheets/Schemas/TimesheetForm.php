<?php

namespace App\Filament\Resources\Timesheets\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TimesheetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Timesheet')
                    ->columns(3)
                    ->schema([
                        Select::make('employee_id')
                            ->label('Karyawan')
                            ->relationship('employee', 'first_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        DatePicker::make('date')
                            ->label('Tanggal')
                            ->required()
                            ->default(now()),
                        TextInput::make('total_hours')
                            ->label('Total Jam')
                            ->numeric()
                            ->suffix('jam'),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'submitted' => 'Diajukan',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                            ])
                            ->default('draft')
                            ->required(),
                        Select::make('approved_by')
                            ->label('Disetujui Oleh')
                            ->relationship('approvedBy', 'first_name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        DateTimePicker::make('submitted_at')
                            ->label('Waktu Pengajuan'),
                        DateTimePicker::make('approved_at')
                            ->label('Waktu Persetujuan'),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
