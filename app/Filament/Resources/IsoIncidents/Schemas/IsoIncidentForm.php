<?php

namespace App\Filament\Resources\IsoIncidents\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class IsoIncidentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Insiden')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(1),
                        TextInput::make('incident_number')
                            ->label('Nomor Insiden')
                            ->disabled()
                            ->hint('Auto-generated'),
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255),
                        Select::make('incident_type')
                            ->label('Tipe Insiden')
                            ->options([
                                'phishing' => 'Phishing',
                                'malware' => 'Malware',
                                'unauthorized_access' => 'Akses Tidak Sah',
                                'ddos' => 'DDoS',
                                'data_leak' => 'Kebocoran Data',
                                'insider' => 'Orang Dalam',
                                'physical' => 'Fisik',
                            ])
                            ->required(),
                        Select::make('severity')
                            ->label('Keparahan')
                            ->options([
                                'low' => 'Rendah',
                                'medium' => 'Sedang',
                                'high' => 'Tinggi',
                                'critical' => 'Kritis',
                            ])
                            ->required(),
                        DateTimePicker::make('detected_at')
                            ->label('Waktu Terdeteksi')
                            ->required()
                            ->default(now()),
                        DateTimePicker::make('resolved_at')
                            ->label('Waktu Penyelesaian'),
                        Toggle::make('reportable_to_regulator')
                            ->label('Wajib Lapor ke Regulator')
                            ->default(false),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'open' => 'Terbuka',
                                'investigating' => 'Investigasi',
                                'contained' => 'Penanganan',
                                'resolved' => 'Selesai',
                                'closed' => 'Ditutup',
                            ])
                            ->default('open'),
                    ]),
                Section::make('Detail Kejadian')
                    ->schema([
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(4)
                            ->required(),
                        Textarea::make('affected_assets')
                            ->label('Aset Terdampak')
                            ->rows(2),
                        TextInput::make('affected_systems')
                            ->label('Sistem Terdampak')
                            ->maxLength(255),
                    ]),
                Section::make('Investigasi')
                    ->schema([
                        Textarea::make('findings')
                            ->label('Temuan')
                            ->rows(3),
                        Textarea::make('root_cause')
                            ->label('Penyebab Utama')
                            ->rows(2),
                        Textarea::make('corrective_actions')
                            ->label('Tindakan Perbaikan')
                            ->rows(3),
                        Textarea::make('preventive_actions')
                            ->label('Tindakan Pencegahan')
                            ->rows(3),
                    ]),
            ]);
    }
}