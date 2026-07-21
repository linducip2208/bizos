<?php

namespace App\Filament\Resources\KpiTemplates\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class KpiTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Template')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama Template')
                            ->required()
                            ->maxLength(255),
                        Select::make('position_id')
                            ->label('Jabatan')
                            ->relationship('position', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Opsional – template khusus per jabatan'),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),

                Section::make('Indikator KPI (Balanced Scorecard)')
                    ->description('Total bobot semua indikator harus 100%')
                    ->schema([
                        Repeater::make('indicators')
                            ->label('Daftar Indikator')
                            ->relationship()
                            ->orderColumn('sort_order')
                            ->addActionLabel('Tambah Indikator')
                            ->columns(3)
                            ->columnSpanFull()
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Indikator')
                                    ->required()
                                    ->maxLength(255),
                                Select::make('category')
                                    ->label('Kategori BSC')
                                    ->options([
                                        'financial' => 'Keuangan',
                                        'customer' => 'Pelanggan',
                                        'internal_process' => 'Proses Internal',
                                        'learning_growth' => 'Pembelajaran & Pertumbuhan',
                                    ])
                                    ->required(),
                                TextInput::make('weight_percent')
                                    ->label('Bobot (%)')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->suffix('%')
                                    ->helperText('Total bobot seluruh indikator harus 100%'),
                                Select::make('target_type')
                                    ->label('Tipe Target')
                                    ->options([
                                        'numeric' => 'Numerik',
                                        'percentage' => 'Persentase',
                                        'boolean' => 'Ya/Tidak',
                                        'rating_1_5' => 'Rating 1-5',
                                    ])
                                    ->required(),
                                TextInput::make('target_value')
                                    ->label('Nilai Target')
                                    ->numeric()
                                    ->nullable(),
                                TextInput::make('measurement_unit')
                                    ->label('Satuan')
                                    ->maxLength(50)
                                    ->placeholder('Rp, %, unit, dll'),
                                TextInput::make('data_source')
                                    ->label('Sumber Data')
                                    ->maxLength(255)
                                    ->placeholder('Sistem CRM, laporan penjualan, dll'),
                                Textarea::make('description')
                                    ->label('Deskripsi')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
