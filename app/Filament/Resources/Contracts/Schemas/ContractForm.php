<?php

namespace App\Filament\Resources\Contracts\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ContractForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kontrak')
                    ->columns(2)
                    ->schema([
                        TextInput::make('contract_number')
                            ->label('Nomor Kontrak')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('title')
                            ->label('Judul Kontrak')
                            ->required()
                            ->maxLength(255),
                        Select::make('contract_type')
                            ->label('Tipe Kontrak')
                            ->options([
                                'service' => 'Layanan',
                                'procurement' => 'Pengadaan',
                                'tenancy' => 'Sewa',
                                'employment' => 'Karyawan',
                                'project' => 'Proyek',
                                'subcontractor' => 'Subkontraktor',
                                'partnership' => 'Kemitraan',
                                'other' => 'Lainnya',
                            ])
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'pending_approval' => 'Menunggu Approval',
                                'active' => 'Aktif',
                                'amended' => 'Amandemen',
                                'expired' => 'Kadaluarsa',
                                'terminated' => 'Dihentikan',
                                'renewed' => 'Diperpanjang',
                            ])
                            ->required()
                            ->default('draft'),
                        Select::make('currency_id')
                            ->label('Mata Uang')
                            ->relationship('currency', 'code')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('value')
                            ->label('Nilai Kontrak')
                            ->numeric()
                            ->nullable(),
                        DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->nullable(),
                        DatePicker::make('end_date')
                            ->label('Tanggal Berakhir')
                            ->nullable()
                            ->afterOrEqual('start_date'),
                        DatePicker::make('renewal_date')
                            ->label('Tanggal Perpanjangan')
                            ->nullable()
                            ->afterOrEqual('end_date'),
                    ]),
                Section::make('Pihak Terkait')
                    ->columns(2)
                    ->schema([
                        Select::make('party_type')
                            ->label('Tipe Pihak')
                            ->options([
                                'client' => 'Klien',
                                'supplier' => 'Supplier',
                                'employee' => 'Karyawan',
                                'partner' => 'Partner',
                            ])
                            ->nullable()
                            ->live(),
                        TextInput::make('party_id')
                            ->label('ID Pihak')
                            ->numeric()
                            ->nullable()
                            ->visible(fn ($get) => $get('party_type') !== null),
                        Select::make('template_id')
                            ->label('Template Dokumen')
                            ->relationship('template', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ]),
                Section::make('Deskripsi & Ketentuan')
                    ->columns(1)
                    ->schema([
                        RichEditor::make('description')
                            ->label('Deskripsi')
                            ->nullable()
                            ->columnSpanFull(),
                        RichEditor::make('terms_conditions')
                            ->label('Syarat & Ketentuan')
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
                Section::make('SLA & Kewajiban')
                    ->columns(1)
                    ->schema([
                        KeyValue::make('sla_details')
                            ->label('Detail SLA')
                            ->keyLabel('Parameter')
                            ->valueLabel('Nilai')
                            ->nullable()
                            ->columnSpanFull(),
                        KeyValue::make('obligations')
                            ->label('Kewajiban Para Pihak')
                            ->keyLabel('Pihak')
                            ->valueLabel('Kewajiban')
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
                Section::make('Penandatanganan & Approval')
                    ->columns(2)
                    ->schema([
                        Select::make('signed_by')
                            ->label('Ditandatangani Oleh')
                            ->relationship('signer', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        DateTimePicker::make('signed_at')
                            ->label('Tanggal Tanda Tangan')
                            ->nullable(),
                        Select::make('approved_by')
                            ->label('Disetujui Oleh')
                            ->relationship('approver', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        DateTimePicker::make('approved_at')
                            ->label('Tanggal Approval')
                            ->nullable(),
                    ]),
                Section::make('Hubungan Kontrak')
                    ->columns(2)
                    ->schema([
                        Select::make('parent_contract_id')
                            ->label('Kontrak Induk')
                            ->relationship('parentContract', 'contract_number')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ]),
                Section::make('Metadata')
                    ->columns(1)
                    ->schema([
                        KeyValue::make('metadata')
                            ->label('Metadata')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
