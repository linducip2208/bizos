<?php

namespace App\Filament\Resources\DailySiteReports\Schemas;

use App\Models\Project;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DailySiteReportForm
{
    public static function configure(Schema $schema): Schema
    {
        $companyId = auth()->user()->company_id;

        return $schema
            ->components([
                Section::make('Informasi Laporan')
                    ->schema([
                        Select::make('project_id')
                            ->label('Proyek')
                            ->options(Project::where('company_id', $companyId)->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        DatePicker::make('report_date')
                            ->label('Tanggal Laporan')
                            ->required()
                            ->default(now()),
                    ])->columns(2),

                Section::make('Cuaca & Tenaga Kerja')
                    ->schema([
                        Select::make('weather')
                            ->label('Cuaca')
                            ->options([
                                'cerah' => 'Cerah',
                                'mendung' => 'Mendung',
                                'hujan' => 'Hujan',
                                'badai' => 'Badai',
                            ])
                            ->nullable(),
                        TextInput::make('temperature')
                            ->label('Suhu (°C)')
                            ->numeric()
                            ->step(0.1),
                        TextInput::make('worker_count')
                            ->label('Jumlah Pekerja')
                            ->integer()
                            ->default(0),
                    ])->columns(3),

                Section::make('Aktivitas')
                    ->schema([
                        KeyValue::make('heavy_equipment_used')
                            ->label('Alat Berat Digunakan')
                            ->keyLabel('Nama Alat')
                            ->valueLabel('Jam Operasi')
                            ->addButtonLabel('Tambah Alat')
                            ->nullable(),
                        KeyValue::make('materials_used')
                            ->label('Material Digunakan')
                            ->keyLabel('Material')
                            ->valueLabel('Kuantitas')
                            ->addButtonLabel('Tambah Material')
                            ->nullable(),
                        Textarea::make('work_description')
                            ->label('Deskripsi Pekerjaan')
                            ->maxLength(2000)
                            ->columnSpanFull(),
                        Textarea::make('issues')
                            ->label('Kendala/Masalah')
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ]),

                Section::make('Dokumentasi')
                    ->schema([
                        FileUpload::make('progress_photo_path')
                            ->label('Foto Progres')
                            ->image()
                            ->directory('site-reports')
                            ->nullable()
                            ->columnSpanFull(),
                        Select::make('approved_by')
                            ->label('Disetujui Oleh')
                            ->options(User::where('company_id', $companyId)->pluck('name', 'id'))
                            ->searchable()
                            ->nullable(),
                    ]),
            ]);
    }
}