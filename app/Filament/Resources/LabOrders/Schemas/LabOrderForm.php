<?php

namespace App\Filament\Resources\LabOrders\Schemas;

use App\Models\Employee;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class LabOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Data Order Lab')
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
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->patient_number} — {$record->first_name} {$record->last_name}")
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('doctor_id')
                            ->label('Dokter Pengirim')
                            ->options(function () {
                                return Employee::where('is_doctor', true)
                                    ->get()
                                    ->mapWithKeys(fn ($e) => [$e->id => "{$e->first_name} {$e->last_name}"]);
                            })
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('medical_record_id')
                            ->label('Rekam Medis')
                            ->relationship('medicalRecord', 'visit_date')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->visit_date->format('d M Y')} — {$record->diagnosis_name}")
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        DatePicker::make('order_date')
                            ->label('Tanggal Order')
                            ->required()
                            ->default(now()),
                        Select::make('lab_type')
                            ->label('Jenis Lab')
                            ->options([
                                'hematology' => 'Hematologi',
                                'chemistry' => 'Kimia',
                                'microbiology' => 'Mikrobiologi',
                                'radiology' => 'Radiologi',
                                'urine' => 'Urinalisis',
                                'other' => 'Lainnya',
                            ])
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'ordered' => 'Diorder',
                                'sample_collected' => 'Sampel Diambil',
                                'in_progress' => 'Sedang Diproses',
                                'completed' => 'Selesai',
                                'reviewed' => 'Sudah Direview',
                            ])
                            ->required()
                            ->default('ordered'),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
