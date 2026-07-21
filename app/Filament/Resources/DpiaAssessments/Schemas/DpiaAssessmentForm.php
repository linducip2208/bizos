<?php

namespace App\Filament\Resources\DpiaAssessments\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DpiaAssessmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi DPIA')
                    ->columns(2)
                    ->schema([
                        Select::make('company_id')
                            ->label('Perusahaan')
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(1),
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('processing_activity')
                            ->label('Aktivitas Pemrosesan')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('data_controller')
                            ->label('Pengendali Data')
                            ->maxLength(255),
                        TextInput::make('data_processor')
                            ->label('Pemroses Data')
                            ->maxLength(255),
                        Select::make('risk_level')
                            ->label('Level Risiko')
                            ->options([
                                'low' => 'Rendah',
                                'medium' => 'Sedang',
                                'high' => 'Tinggi',
                                'critical' => 'Kritis',
                            ])
                            ->default('low'),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'in_review' => 'Review',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                'needs_revision' => 'Perlu Revisi',
                            ])
                            ->default('draft'),
                    ]),
                Section::make('Cakupan Data')
                    ->columns(2)
                    ->schema([
                        TagsInput::make('data_types')
                            ->label('Tipe Data')
                            ->separator(',')
                            ->placeholder('personal, sensitive, financial...'),
                        TagsInput::make('data_subjects')
                            ->label('Subjek Data')
                            ->separator(',')
                            ->placeholder('employees, clients, suppliers...'),
                    ]),
                Section::make('Analisis')
                    ->schema([
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(3),
                        RichEditor::make('necessity_proportionality')
                            ->label('Kebutuhan & Proporsionalitas'),
                        KeyValue::make('risks')
                            ->label('Risiko Teridentifikasi')
                            ->addButtonLabel('Tambah Risiko')
                            ->keyLabel('Risiko')
                            ->valueLabel('Deskripsi'),
                        KeyValue::make('mitigations')
                            ->label('Mitigasi')
                            ->addButtonLabel('Tambah Mitigasi')
                            ->keyLabel('Kontrol')
                            ->valueLabel('Deskripsi'),
                        Textarea::make('review_notes')
                            ->label('Catatan Review')
                            ->rows(2),
                    ]),
            ]);
    }
}