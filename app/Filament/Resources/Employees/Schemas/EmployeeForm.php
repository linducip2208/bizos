<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Data Pribadi')
                    ->columns(3)
                    ->schema([
                        TextInput::make('first_name')
                            ->label('Nama Depan')
                            ->required()
                            ->maxLength(100),
                        TextInput::make('last_name')
                            ->label('Nama Belakang')
                            ->maxLength(100),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Telepon')
                            ->tel()
                            ->maxLength(30),
                        Select::make('gender')
                            ->label('Jenis Kelamin')
                            ->options([
                                'male' => 'Pria',
                                'female' => 'Wanita',
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
                        Select::make('marital_status')
                            ->label('Status Pernikahan')
                            ->options([
                                'single' => 'Belum Menikah',
                                'married' => 'Menikah',
                                'divorced' => 'Cerai',
                                'widowed' => 'Duda/Janda',
                            ]),
                        TextInput::make('nationality')
                            ->label('Kewarganegaraan')
                            ->maxLength(50)
                            ->default('Indonesia'),
                    ]),

                Section::make('Data Kepegawaian')
                    ->columns(3)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('branch_id')
                            ->label('Cabang')
                            ->relationship('branch', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('department_id')
                            ->label('Departemen')
                            ->relationship('department', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('position_id')
                            ->label('Jabatan')
                            ->relationship('position', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('designation_id')
                            ->label('Penunjukan')
                            ->relationship('designation', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Select::make('grade_id')
                            ->label('Grade')
                            ->relationship('grade', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('employee_code')
                            ->label('NIP / ID Karyawan')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        Select::make('employee_type')
                            ->label('Tipe Karyawan')
                            ->options([
                                'permanent' => 'Permanen',
                                'contract' => 'Kontrak',
                                'probation' => 'Percobaan',
                                'intern' => 'Magang',
                                'freelance' => 'Freelance',
                                'part_time' => 'Paruh Waktu',
                            ])
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Aktif',
                                'inactive' => 'Nonaktif',
                                'terminated' => 'Diberhentikan',
                                'resigned' => 'Mengundurkan Diri',
                                'retired' => 'Pensiun',
                            ])
                            ->default('active')
                            ->required()
                            ->live(),
                        DatePicker::make('join_date')
                            ->label('Tanggal Bergabung')
                            ->required(),
                        DatePicker::make('contract_start')
                            ->label('Awal Kontrak'),
                        DatePicker::make('contract_end')
                            ->label('Akhir Kontrak'),
                    ]),

                Section::make('Data Gaji')
                    ->columns(3)
                    ->schema([
                        TextInput::make('basic_salary')
                            ->label('Gaji Pokok')
                            ->numeric()
                            ->required()
                            ->prefix('Rp'),
                        TextInput::make('hourly_rate')
                            ->label('Tarif Per Jam')
                            ->numeric()
                            ->prefix('Rp'),
                        TextInput::make('overtime_rate')
                            ->label('Tarif Lembur')
                            ->numeric()
                            ->prefix('Rp'),
                    ]),

                Section::make('Data Bank')
                    ->columns(3)
                    ->schema([
                        Select::make('bank_name')
                            ->label('Nama Bank')
                            ->options([
                                'BCA' => 'BCA',
                                'Mandiri' => 'Mandiri',
                                'BNI' => 'BNI',
                                'BRI' => 'BRI',
                                'CIMB Niaga' => 'CIMB Niaga',
                                'Permata' => 'Permata',
                                'Danamon' => 'Danamon',
                                'Maybank' => 'Maybank',
                                'OCBC NISP' => 'OCBC NISP',
                                'Panin' => 'Panin',
                                'BTN' => 'BTN',
                                'Bank Syariah Indonesia' => 'BSI',
                                'Bank Mega' => 'Bank Mega',
                                'BTPN' => 'BTPN',
                                'Jenius' => 'Jenius',
                                'GoPay' => 'GoPay',
                                'OVO' => 'OVO',
                                'DANA' => 'DANA',
                            ])
                            ->searchable(),
                        TextInput::make('bank_account_number')
                            ->label('Nomor Rekening')
                            ->maxLength(50),
                        TextInput::make('bank_account_name')
                            ->label('Nama Pemilik Rekening')
                            ->maxLength(255),
                    ]),

                Section::make('Data Identitas')
                    ->columns(4)
                    ->schema([
                        TextInput::make('id_number')
                            ->label('NIK KTP')
                            ->maxLength(50),
                        TextInput::make('tax_number')
                            ->label('NPWP')
                            ->maxLength(50),
                        TextInput::make('bpjs_kesehatan')
                            ->label('BPJS Kesehatan')
                            ->maxLength(50),
                        TextInput::make('bpjs_ketenagakerjaan')
                            ->label('BPJS Ketenagakerjaan')
                            ->maxLength(50),
                    ]),

                Section::make('Foto')
                    ->columns(1)
                    ->schema([
                        FileUpload::make('photo')
                            ->label('Foto Karyawan')
                            ->image()
                            ->directory('employees/photos')
                            ->imagePreviewHeight('200')
                            ->maxSize(2048),
                    ]),

                Section::make('Data Pemberhentian')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('termination_date')
                            ->label('Tanggal Pemberhentian')
                            ->visible(fn ($get) => in_array($get('status'), ['terminated', 'resigned', 'retired'])),
                        Textarea::make('termination_reason')
                            ->label('Alasan Pemberhentian')
                            ->rows(3)
                            ->visible(fn ($get) => in_array($get('status'), ['terminated', 'resigned', 'retired'])),
                    ])
                    ->visible(fn ($get) => in_array($get('status'), ['terminated', 'resigned', 'retired'])),
            ]);
    }
}