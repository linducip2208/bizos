<?php

namespace App\Filament\Resources\IsoRisks\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class IsoRiskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Aset & Risiko')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(1),
                        TextInput::make('asset_name')
                            ->label('Nama Aset')
                            ->required()
                            ->maxLength(255),
                        Select::make('asset_type')
                            ->label('Tipe Aset')
                            ->options([
                                'hardware' => 'Hardware',
                                'software' => 'Software',
                                'data' => 'Data',
                                'network' => 'Jaringan',
                                'people' => 'SDM',
                                'facility' => 'Fasilitas',
                            ])
                            ->required(),
                        Textarea::make('asset_description')
                            ->label('Deskripsi Aset')
                            ->rows(2),
                        TextInput::make('threat')
                            ->label('Ancaman')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('vulnerability')
                            ->label('Kerentanan')
                            ->required()
                            ->maxLength(255),
                    ]),
                Section::make('Penilaian Risiko')
                    ->columns(3)
                    ->schema([
                        Select::make('likelihood')
                            ->label('Kemungkinan')
                            ->options([
                                'rare' => '1 - Sangat Jarang',
                                'unlikely' => '2 - Tidak Mungkin',
                                'possible' => '3 - Mungkin',
                                'likely' => '4 - Kemungkinan Besar',
                                'almost_certain' => '5 - Hampir Pasti',
                            ])
                            ->required()
                            ->default('possible'),
                        Select::make('impact')
                            ->label('Dampak')
                            ->options([
                                'insignificant' => '1 - Tidak Signifikan',
                                'minor' => '2 - Kecil',
                                'moderate' => '3 - Sedang',
                                'major' => '4 - Besar',
                                'catastrophic' => '5 - Bencana',
                            ])
                            ->required()
                            ->default('moderate'),
                        Select::make('treatment')
                            ->label('Perlakuan')
                            ->options([
                                'accept' => 'Terima',
                                'mitigate' => 'Mitigasi',
                                'transfer' => 'Transfer',
                                'avoid' => 'Hindari',
                            ])
                            ->required()
                            ->default('mitigate'),
                    ]),
                Section::make('Kontrol & Tindak Lanjut')
                    ->columns(2)
                    ->schema([
                        TextInput::make('iso_control_ref')
                            ->label('Referensi Kontrol ISO')
                            ->placeholder('A.5.1.1')
                            ->maxLength(50),
                        Textarea::make('existing_controls')
                            ->label('Kontrol yang Ada')
                            ->rows(2),
                        Textarea::make('treatment_plan')
                            ->label('Rencana Perlakuan')
                            ->rows(3),
                        Textarea::make('applied_controls')
                            ->label('Kontrol Diterapkan')
                            ->rows(2),
                        Select::make('owner_id')
                            ->label('Pemilik Risiko')
                            ->relationship('owner', 'name')
                            ->searchable()
                            ->preload(),
                        DatePicker::make('review_due')
                            ->label('Review Berikutnya'),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'open' => 'Terbuka',
                                'in_treatment' => 'Dalam Perlakuan',
                                'treated' => 'Ditangani',
                                'accepted' => 'Diterima',
                                'closed' => 'Ditutup',
                            ])
                            ->default('open'),
                    ]),
            ]);
    }
}