<?php

namespace App\Filament\Resources\FeedbackAnswers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FeedbackAnswersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reviewer.reviewee.first_name')
                    ->label('Karyawan Dinilai')
                    ->state(fn ($record) => trim(($record->reviewer?->reviewee?->first_name ?? '') . ' ' . ($record->reviewer?->reviewee?->last_name ?? '')))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('question.question')
                    ->label('Pertanyaan')
                    ->searchable()
                    ->limit(80),
                TextColumn::make('rating')
                    ->label('Rating')
                    ->sortable()
                    ->numeric(),
                TextColumn::make('text_answer')
                    ->label('Jawaban')
                    ->limit(60)
                    ->placeholder('-'),
            ])
            ->defaultSort('reviewer_id', 'asc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
