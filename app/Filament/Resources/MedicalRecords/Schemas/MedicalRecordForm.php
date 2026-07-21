<?php

namespace App\Filament\Resources\MedicalRecords\Schemas;

use App\Models\Employee;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class MedicalRecordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Data Kunjungan')
                    ->columns(3)
                    ->schema([
                        Select::make('patient_id')
                            ->label('Pasien')
                            ->relationship('patient', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->patient_number} — {$record->first_name} {$record->last_name}")
                            ->searchable()
                            ->preload()
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
                        Select::make('appointment_id')
                            ->label('Janji Temu')
                            ->relationship('appointment', 'appointment_date')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->appointment_date->format('d M Y')} — {$record->patient?->first_name}")
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        DatePicker::make('visit_date')
                            ->label('Tanggal Kunjungan')
                            ->required()
                            ->default(now()),
                        TextInput::make('diagnosis_code')
                            ->label('Kode ICD-10')
                            ->maxLength(20)
                            ->helperText('Contoh: I10, A09, E11'),
                        TextInput::make('diagnosis_name')
                            ->label('Nama Diagnosis')
                            ->maxLength(255),
                        Toggle::make('is_final')
                            ->label('Diagnosis Final')
                            ->default(false),
                    ]),
                Tabs::make('SOAP')
                    ->tabs([
                        Tabs\Tab::make('S — Subjective')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema([
                                Section::make('Keluhan Pasien (Subjective)')
                                    ->schema([
                                        \Filament\Forms\Components\RichEditor::make('subjective')
                                            ->label('Keluhan Utama & Riwayat')
                                            ->required()
                                            ->columnSpanFull()
                                            ->helperText('Catat keluhan utama, riwayat penyakit sekarang, riwayat penyakit dahulu, riwayat keluarga'),
                                    ]),
                            ]),
                        Tabs\Tab::make('O — Objective')
                            ->icon('heroicon-o-eye')
                            ->schema([
                                Section::make('Pemeriksaan Fisik (Objective)')
                                    ->schema([
                                        \Filament\Forms\Components\RichEditor::make('objective')
                                            ->label('Hasil Pemeriksaan Fisik')
                                            ->required()
                                            ->columnSpanFull(),
                                        KeyValue::make('vital_signs')
                                            ->label('Tanda Vital')
                                            ->keyLabel('Parameter')
                                            ->valueLabel('Nilai')
                                            ->addButtonLabel('Tambah Parameter')
                                            ->default([
                                                'blood_pressure' => '',
                                                'temperature' => '',
                                                'pulse' => '',
                                                'respiratory_rate' => '',
                                                'weight' => '',
                                                'height' => '',
                                            ])
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Tabs\Tab::make('A — Assessment')
                            ->icon('heroicon-o-light-bulb')
                            ->schema([
                                Section::make('Diagnosis (Assessment)')
                                    ->schema([
                                        \Filament\Forms\Components\RichEditor::make('assessment')
                                            ->label('Diagnosis / Penilaian')
                                            ->required()
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Tabs\Tab::make('P — Plan')
                            ->icon('heroicon-o-clipboard-document-check')
                            ->schema([
                                Section::make('Rencana Terapi (Plan)')
                                    ->schema([
                                        \Filament\Forms\Components\RichEditor::make('plan')
                                            ->label('Rencana Terapi & Tindak Lanjut')
                                            ->required()
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
