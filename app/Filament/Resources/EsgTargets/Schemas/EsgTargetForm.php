<?php

namespace App\Filament\Resources\EsgTargets\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class EsgTargetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Target ESG')
                    ->columns(3)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('category')
                            ->label('Kategori')
                            ->options([
                                'environmental' => 'Lingkungan',
                                'social' => 'Sosial',
                                'governance' => 'Tata Kelola',
                            ])
                            ->required()
                            ->live(),
                        Select::make('metric')
                            ->label('Metrik')
                            ->options(fn($get) => match ($get('category')) {
                                'environmental' => [
                                    'carbon_emissions' => 'Emisi Karbon',
                                    'waste_reduction' => 'Pengurangan Limbah',
                                    'water_reduction' => 'Pengurangan Air',
                                    'energy_efficiency' => 'Efisiensi Energi',
                                    'renewable_energy' => 'Energi Terbarukan',
                                ],
                                'social' => [
                                    'gender_diversity' => 'Diversitas Gender',
                                    'safety_incident' => 'Insiden Keselamatan',
                                    'training_hours' => 'Jam Pelatihan',
                                    'employee_satisfaction' => 'Kepuasan Karyawan',
                                    'turnover_rate' => 'Tingkat Turnover',
                                ],
                                'governance' => [
                                    'board_independence' => 'Independensi Dewan',
                                    'board_diversity' => 'Diversitas Dewan',
                                    'compliance_rate' => 'Tingkat Kepatuhan',
                                    'whistleblower_response' => 'Respons Whistleblower',
                                    'data_breach' => 'Pelanggaran Data',
                                ],
                                default => [],
                            })
                            ->required(),
                        TextInput::make('metric_label')
                            ->label('Label Metrik')
                            ->required()
                            ->maxLength(200),
                        TextInput::make('unit')
                            ->label('Satuan')
                            ->required()
                            ->maxLength(50),
                        TextInput::make('baseline_value')
                            ->label('Nilai Baseline')
                            ->numeric()
                            ->nullable(),
                        TextInput::make('target_value')
                            ->label('Nilai Target')
                            ->numeric()
                            ->required(),
                        TextInput::make('current_value')
                            ->label('Nilai Saat Ini')
                            ->numeric()
                            ->default(0),
                        DatePicker::make('deadline')
                            ->label('Tenggat Waktu')
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'on_track' => 'Sesuai Jalur',
                                'at_risk' => 'Berisiko',
                                'behind' => 'Tertinggal',
                                'achieved' => 'Tercapai',
                                'abandoned' => 'Dibatalkan',
                            ])
                            ->default('on_track')
                            ->required(),
                        TextInput::make('responsible_person')
                            ->label('Penanggung Jawab')
                            ->maxLength(200)
                            ->nullable(),
                        TextInput::make('framework_reference')
                            ->label('Referensi Kerangka')
                            ->helperText('Contoh: GRI 302, SASB EM-EP, POJK 51')
                            ->maxLength(100)
                            ->nullable(),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
