<?php

namespace App\Filament\Resources\Attendances\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Absensi')
                    ->columns(3)
                    ->schema([
                        Select::make('employee_id')
                            ->label('Karyawan')
                            ->relationship('employee', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => trim($record->first_name . ' ' . $record->last_name))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('shift_id')
                            ->label('Shift')
                            ->relationship('shift', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        DatePicker::make('date')
                            ->label('Tanggal')
                            ->required(),
                        DateTimePicker::make('clock_in')
                            ->label('Jam Masuk')
                            ->nullable(),
                        DateTimePicker::make('clock_out')
                            ->label('Jam Keluar')
                            ->nullable(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'present' => 'Hadir',
                                'late' => 'Terlambat',
                                'absent' => 'Tidak Hadir',
                                'half_day' => 'Setengah Hari',
                                'wfh' => 'WFH',
                                'leave' => 'Cuti',
                                'holiday' => 'Libur',
                                'sick' => 'Sakit',
                            ])
                            ->default('present')
                            ->required(),
                        TextInput::make('late_minutes')
                            ->label('Menit Keterlambatan')
                            ->numeric()
                            ->default(0),
                        Select::make('work_type')
                            ->label('Tipe Kerja')
                            ->options([
                                'wfo' => 'WFO',
                                'wfh' => 'WFH',
                                'field' => 'Lapangan',
                            ])
                            ->default('wfo'),
                        Select::make('payroll_period_id')
                            ->label('Periode Gaji')
                            ->relationship('payrollPeriod', 'period_code')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}