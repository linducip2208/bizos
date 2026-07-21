<?php

namespace App\Filament\Resources\PerformanceReviews\Schemas;

use App\Models\PerformanceReview;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PerformanceReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Review')
                    ->columns(2)
                    ->schema([
                        Select::make('cycle_id')
                            ->label('Siklus Performa')
                            ->relationship('cycle', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('employee_id')
                            ->label('Karyawan')
                            ->relationship('employee', 'first_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('reviewer_id')
                            ->label('Penilai (Atasan)')
                            ->relationship('reviewer', 'first_name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('kpi_template_id')
                            ->label('Template KPI')
                            ->relationship('kpiTemplate', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'self_assessment' => 'Self Assessment',
                                'manager_review' => 'Review Manager',
                                'hr_calibration' => 'Kalibrasi HR',
                                'completed' => 'Selesai',
                            ])
                            ->default('self_assessment')
                            ->required(),
                    ]),

                Section::make('Skor')
                    ->columns(2)
                    ->visible(fn ($record) => $record !== null)
                    ->schema([
                        TextInput::make('employee_self_score')
                            ->label('Skor Self Assessment')
                            ->numeric()
                            ->disabled(),
                        TextInput::make('reviewer_score')
                            ->label('Skor Manager')
                            ->numeric()
                            ->disabled(),
                        TextInput::make('calibration_score')
                            ->label('Skor Kalibrasi HR')
                            ->numeric()
                            ->disabled(),
                        TextInput::make('final_score')
                            ->label('Skor Akhir')
                            ->numeric()
                            ->disabled(),
                    ]),

                Section::make('Perbandingan Skor Per Indikator')
                    ->visible(fn ($record) => $record && $record->scores()->count() > 0)
                    ->schema(function ($record) {
                        if (!$record) return [];
                        $fields = [];
                        foreach ($record->scores as $score) {
                            $fields[] = Section::make($score->indicator?->name ?? "Indikator #{$score->id}")
                                ->columns(4)
                                ->schema([
                                    Placeholder::make("indicator_{$score->id}_weight")
                                        ->label('Bobot')
                                        ->content($score->weight . '%'),
                                    Placeholder::make("indicator_{$score->id}_self")
                                        ->label('Self Score')
                                        ->content($score->employee_score ?? '-'),
                                    Placeholder::make("indicator_{$score->id}_reviewer")
                                        ->label('Reviewer Score')
                                        ->content($score->reviewer_score ?? '-'),
                                    Placeholder::make("indicator_{$score->id}_calibration")
                                        ->label('Calibration Score')
                                        ->content($score->calibration_score ?? '-'),
                                ]);
                        }
                        return $fields;
                    }),
            ]);
    }
}