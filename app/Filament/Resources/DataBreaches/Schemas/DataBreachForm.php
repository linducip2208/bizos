<?php

namespace App\Filament\Resources\DataBreaches\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DataBreachForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pelanggaran')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(1),
                        Select::make('breach_type')
                            ->label('Tipe Pelanggaran')
                            ->options([
                                'unauthorized_access' => 'Akses Tidak Sah',
                                'data_leak' => 'Kebocoran Data',
                                'malware' => 'Serangan Malware',
                                'physical_theft' => 'Pencurian Fisik',
                                'insider' => 'Orang Dalam',
                                'third_party' => 'Pihak Ketiga',
                            ])
                            ->required(),
                        Select::make('severity')
                            ->label('Tingkat Keparahan')
                            ->options([
                                'low' => 'Rendah',
                                'medium' => 'Sedang',
                                'high' => 'Tinggi',
                                'critical' => 'Kritis',
                            ])
                            ->required(),
                        DateTimePicker::make('discovered_at')
                            ->label('Waktu Ditemukan')
                            ->required()
                            ->default(now()),
                        TextInput::make('affected_records_count')
                            ->label('Jumlah Data Terdampak')
                            ->numeric()
                            ->default(0)
                            ->required(),
                        TagsInput::make('affected_data_types')
                            ->label('Tipe Data Terdampak')
                            ->placeholder('nama, email, phone, ktp...')
                            ->separator(','),
                        DateTimePicker::make('contained_at')
                            ->label('Waktu Penanganan'),
                        DateTimePicker::make('resolved_at')
                            ->label('Waktu Penyelesaian'),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'open' => 'Terbuka',
                                'investigating' => 'Investigasi',
                                'contained' => 'Tertangani',
                                'resolved' => 'Terselesaikan',
                                'closed' => 'Ditutup',
                            ])
                            ->default('open'),
                    ]),
                Section::make('Detail Investigasi')
                    ->columns(1)
                    ->schema([
                        Textarea::make('description')
                            ->label('Deskripsi Kejadian')
                            ->rows(4)
                            ->required(),
                        Textarea::make('root_cause')
                            ->label('Penyebab Utama')
                            ->rows(3),
                        Textarea::make('immediate_actions')
                            ->label('Tindakan Langsung')
                            ->rows(3),
                        Textarea::make('corrective_actions')
                            ->label('Tindakan Perbaikan')
                            ->rows(3),
                    ]),
                Section::make('Notifikasi Regulator')
                    ->columns(2)
                    ->schema([
                        DateTimePicker::make('notified_dpa_at')
                            ->label('Notifikasi ke Kominfo')
                            ->hint('Batas: 3×24 jam per UU PDP'),
                        DateTimePicker::make('notified_subjects_at')
                            ->label('Notifikasi ke Subjek Data'),
                        TextInput::make('dpa_report_number')
                            ->label('Nomor Laporan DPA'),
                    ]),
            ]);
    }
}
