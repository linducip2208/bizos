<?php

namespace App\Filament\Resources\BpjsClaims\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BpjsClaimForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Data Klaim BPJS')
                    ->columns(3)
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
                        Select::make('medical_record_id')
                            ->label('Rekam Medis')
                            ->relationship('medicalRecord', 'visit_date')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->visit_date->format('d M Y')} — {$record->diagnosis_name}")
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('claim_number')
                            ->label('No. Klaim')
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Otomatis dibuat saat simpan'),
                        TextInput::make('sep_number')
                            ->label('No. SEP')
                            ->maxLength(50)
                            ->placeholder('Nomor Surat Eligibilitas Peserta'),
                        TextInput::make('ina_cbgs_code')
                            ->label('Kode INA-CBGs')
                            ->maxLength(20)
                            ->placeholder('Contoh: A-4-14-I'),
                        TextInput::make('ina_cbgs_description')
                            ->label('Deskripsi INA-CBGs')
                            ->maxLength(255),
                        TextInput::make('claim_amount')
                            ->label('Jumlah Klaim')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->default(0),
                        TextInput::make('approved_amount')
                            ->label('Jumlah Disetujui')
                            ->numeric()
                            ->prefix('Rp'),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'submitted' => 'Terkirim',
                                'pending' => 'Pending',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                'paid' => 'Dibayar',
                            ])
                            ->required()
                            ->default('draft'),
                        DateTimePicker::make('submitted_at')
                            ->label('Tanggal Kirim'),
                        DateTimePicker::make('approved_at')
                            ->label('Tanggal Disetujui'),
                        Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->rows(2)
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}