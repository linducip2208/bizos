<?php

namespace App\Filament\Resources\ConsentRecords\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ConsentRecordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Persetujuan')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(1),
                        Select::make('person_type')
                            ->label('Tipe Subjek')
                            ->options([
                                'employee' => 'Karyawan',
                                'client' => 'Klien',
                                'supplier' => 'Supplier',
                            ])
                            ->required(),
                        Select::make('purpose')
                            ->label('Tujuan')
                            ->options([
                                'marketing' => 'Pemasaran',
                                'data_sharing' => 'Berbagi Data',
                                'biometric' => 'Data Biometrik',
                                'location' => 'Data Lokasi',
                                'analytics' => 'Analitik',
                                'profiling' => 'Profil Otomatis',
                                'third_party' => 'Pihak Ketiga',
                            ])
                            ->required()
                            ->searchable(),
                        Select::make('method')
                            ->label('Metode')
                            ->options([
                                'written' => 'Tertulis',
                                'electronic' => 'Elektronik',
                                'implied' => 'Implied',
                                'verbal' => 'Verbal',
                            ])
                            ->required(),
                        DateTimePicker::make('consented_at')
                            ->label('Tanggal Persetujuan')
                            ->default(now())
                            ->required(),
                        DateTimePicker::make('expires_at')
                            ->label('Kedaluwarsa')
                            ->hint('Kosongkan jika tidak ada batas waktu'),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Aktif',
                                'withdrawn' => 'Ditarik',
                                'expired' => 'Kedaluwarsa',
                            ])
                            ->default('active'),
                    ]),
                Section::make('Detail')
                    ->schema([
                        Textarea::make('scope_description')
                            ->label('Deskripsi Cakupan')
                            ->rows(2)
                            ->hint('Jelaskan data apa yang dicakup persetujuan ini'),
                        Textarea::make('withdrawal_reason')
                            ->label('Alasan Penarikan')
                            ->rows(2)
                            ->visible(fn($get) => $get('status') === 'withdrawn'),
                    ]),
            ]);
    }
}