<?php

namespace App\Filament\Resources\Patients\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Schema;

class PatientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Wizard::make([
                    Wizard\Step::make('Identitas')
                        ->icon('heroicon-o-identification')
                        ->schema([
                            Section::make('Data Diri')
                                ->columns(3)
                                ->schema([
                                    Select::make('company_id')
                                        ->label('Perusahaan')
                                        ->relationship('company', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->required(),
                                    TextInput::make('patient_number')
                                        ->label('No. RM')
                                        ->disabled()
                                        ->dehydrated(false)
                                        ->helperText('Otomatis dibuat saat pendaftaran'),
                                    DatePicker::make('registered_at')
                                        ->label('Tanggal Daftar')
                                        ->default(now())
                                        ->required(),
                                    TextInput::make('first_name')
                                        ->label('Nama Depan')
                                        ->required()
                                        ->maxLength(100),
                                    TextInput::make('last_name')
                                        ->label('Nama Belakang')
                                        ->maxLength(100),
                                    Select::make('gender')
                                        ->label('Jenis Kelamin')
                                        ->options([
                                            'male' => 'Laki-laki',
                                            'female' => 'Perempuan',
                                            'other' => 'Lainnya',
                                        ])
                                        ->required(),
                                    DatePicker::make('birth_date')
                                        ->label('Tanggal Lahir'),
                                    TextInput::make('birth_place')
                                        ->label('Tempat Lahir')
                                        ->maxLength(100),
                                    Select::make('religion')
                                        ->label('Agama')
                                        ->options([
                                            'Islam' => 'Islam',
                                            'Kristen' => 'Kristen',
                                            'Katolik' => 'Katolik',
                                            'Hindu' => 'Hindu',
                                            'Buddha' => 'Buddha',
                                            'Konghucu' => 'Konghucu',
                                            'Lainnya' => 'Lainnya',
                                        ]),
                                    Select::make('blood_type')
                                        ->label('Golongan Darah')
                                        ->options([
                                            'A' => 'A',
                                            'B' => 'B',
                                            'AB' => 'AB',
                                            'O' => 'O',
                                            'unknown' => 'Tidak Diketahui',
                                        ])
                                        ->default('unknown'),
                                    TextInput::make('nik')
                                        ->label('NIK (KTP)')
                                        ->maxLength(16)
                                        ->helperText('16 digit Nomor Induk Kependudukan'),
                                    TextInput::make('bpjs_number')
                                        ->label('No. BPJS')
                                        ->maxLength(50),
                                    TextInput::make('phone')
                                        ->label('Telepon')
                                        ->tel()
                                        ->maxLength(30),
                                    TextInput::make('email')
                                        ->label('Email')
                                        ->email()
                                        ->maxLength(255),
                                ]),
                        ]),
                    Wizard\Step::make('Alamat & Kontak Darurat')
                        ->icon('heroicon-o-map-pin')
                        ->schema([
                            Section::make('Alamat')
                                ->columns(2)
                                ->schema([
                                    Textarea::make('address')
                                        ->label('Alamat')
                                        ->rows(3)
                                        ->columnSpanFull(),
                                    TextInput::make('city')
                                        ->label('Kota')
                                        ->maxLength(100),
                                    TextInput::make('province')
                                        ->label('Provinsi')
                                        ->maxLength(100),
                                ]),
                            Section::make('Kontak Darurat')
                                ->columns(2)
                                ->schema([
                                    TextInput::make('emergency_contact_name')
                                        ->label('Nama Kontak Darurat')
                                        ->maxLength(100),
                                    TextInput::make('emergency_contact_phone')
                                        ->label('Telepon Kontak Darurat')
                                        ->tel()
                                        ->maxLength(30),
                                ]),
                        ]),
                    Wizard\Step::make('Riwayat Medis')
                        ->icon('heroicon-o-clipboard-document-list')
                        ->schema([
                            Section::make('Informasi Medis')
                                ->schema([
                                    Select::make('allergies')
                                        ->label('Alergi')
                                        ->multiple()
                                        ->options([
                                            'penicillin' => 'Penisilin',
                                            'sulfa' => 'Sulfa',
                                            'nsaid' => 'NSAID (Aspirin/Ibuprofen)',
                                            'latex' => 'Lateks',
                                            'seafood' => 'Seafood',
                                            'nuts' => 'Kacang-kacangan',
                                            'eggs' => 'Telur',
                                            'milk' => 'Susu',
                                            'pollen' => 'Serbuk Bunga',
                                        ])
                                        ->columnSpanFull(),
                                    RichEditor::make('medical_history_notes')
                                        ->label('Catatan Riwayat Medis')
                                        ->columnSpanFull(),
                                    Toggle::make('is_active')
                                        ->label('Pasien Aktif')
                                        ->default(true),
                                ]),
                        ]),
                ])
                    ->columnSpanFull(),
            ]);
    }
}
