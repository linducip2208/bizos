<?php

namespace App\Filament\Resources\IsoAudits\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class IsoAuditForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Audit')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(1),
                        TextInput::make('audit_number')
                            ->label('Nomor Audit')
                            ->disabled()
                            ->hint('Auto-generated'),
                        TextInput::make('title')
                            ->label('Judul Audit')
                            ->required()
                            ->maxLength(255),
                        Select::make('audit_type')
                            ->label('Tipe Audit')
                            ->options([
                                'internal' => 'Internal',
                                'external' => 'Eksternal',
                                'surveillance' => 'Surveillance',
                                'certification' => 'Sertifikasi',
                                'recertification' => 'Resertifikasi',
                            ])
                            ->required(),
                        TextInput::make('scope')
                            ->label('Ruang Lingkup')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('auditor_name')
                            ->label('Nama Auditor')
                            ->maxLength(255),
                        TextInput::make('auditor_external')
                            ->label('Auditor/Badan Eksternal')
                            ->maxLength(255),
                        Select::make('lead_auditor_id')
                            ->label('Lead Auditor (Internal)')
                            ->relationship('leadAuditor', 'name')
                            ->searchable()
                            ->preload(),
                        DatePicker::make('planned_date')
                            ->label('Tanggal Rencana')
                            ->required(),
                        DatePicker::make('actual_date')
                            ->label('Tanggal Aktual'),
                        DatePicker::make('completed_date')
                            ->label('Tanggal Selesai'),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'planned' => 'Direncanakan',
                                'in_progress' => 'Sedang Berjalan',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->default('planned'),
                        Select::make('result')
                            ->label('Hasil')
                            ->options([
                                'pass' => 'Lulus',
                                'pass_with_observation' => 'Lulus dgn Observasi',
                                'fail' => 'Gagal',
                                'pending' => 'Menunggu',
                            ]),
                    ]),
                Section::make('Ringkasan & Kesimpulan')
                    ->schema([
                        Textarea::make('criteria')
                            ->label('Kriteria Audit')
                            ->rows(2),
                        Textarea::make('summary')
                            ->label('Ringkasan')
                            ->rows(3),
                        Textarea::make('conclusion')
                            ->label('Kesimpulan')
                            ->rows(3),
                    ]),
            ]);
    }
}