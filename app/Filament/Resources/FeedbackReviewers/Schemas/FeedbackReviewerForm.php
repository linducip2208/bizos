<?php

namespace App\Filament\Resources\FeedbackReviewers\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class FeedbackReviewerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Reviewer Feedback')
                    ->columns(2)
                    ->schema([
                        Select::make('cycle_id')
                            ->label('Siklus Feedback')
                            ->relationship('cycle', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('reviewee_id')
                            ->label('Karyawan Dinilai')
                            ->relationship('reviewee', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => trim($record->first_name . ' ' . $record->last_name))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('reviewer_id')
                            ->label('Reviewer')
                            ->relationship('reviewer', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => trim($record->first_name . ' ' . $record->last_name))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('reviewer_type')
                            ->label('Tipe Reviewer')
                            ->options([
                                'self' => 'Diri Sendiri',
                                'peer' => 'Rekan Kerja',
                                'manager' => 'Atasan',
                                'subordinate' => 'Bawahan',
                            ])
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'in_progress' => 'Sedang Diisi',
                                'completed' => 'Selesai',
                            ])
                            ->default('pending')
                            ->required(),
                    ]),
            ]);
    }
}
