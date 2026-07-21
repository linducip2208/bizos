<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Models\Employee;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class AppointmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Data Janji Temu')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('patient_id')
                            ->label('Pasien')
                            ->relationship('patient', 'first_name')
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->patient_number} — {$record->first_name} {$record->last_name}")
                            ->required(),
                        Select::make('doctor_id')
                            ->label('Dokter')
                            ->options(function () {
                                return Employee::where('is_doctor', true)
                                    ->get()
                                    ->mapWithKeys(fn ($e) => [$e->id => "{$e->first_name} {$e->last_name}" . ($e->specialization ? " ({$e->specialization})" : '')]);
                            })
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('appointment_type')
                            ->label('Jenis Kunjungan')
                            ->options([
                                'consultation' => 'Konsultasi',
                                'treatment' => 'Tindakan / Perawatan',
                                'checkup' => 'Check-up',
                                'vaccination' => 'Vaksinasi',
                            ])
                            ->required()
                            ->default('consultation'),
                        DatePicker::make('appointment_date')
                            ->label('Tanggal')
                            ->required()
                            ->minDate(now()),
                        TimePicker::make('start_time')
                            ->label('Jam Mulai')
                            ->required()
                            ->withoutSeconds(),
                        TimePicker::make('end_time')
                            ->label('Jam Selesai')
                            ->required()
                            ->withoutSeconds()
                            ->after('start_time'),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'scheduled' => 'Terjadwal',
                                'confirmed' => 'Dikonfirmasi',
                                'arrived' => 'Sudah Datang',
                                'in_progress' => 'Sedang Diperiksa',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                                'no_show' => 'Tidak Hadir',
                            ])
                            ->required()
                            ->default('scheduled'),
                        TextInput::make('queue_number')
                            ->label('Nomor Antrian')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Otomatis ditentukan sistem'),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}