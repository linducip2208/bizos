<?php

namespace App\Filament\Resources\QuizQuestions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuizQuestionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('quiz.title')
                    ->label('Kuis')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('question')
                    ->label('Pertanyaan')
                    ->limit(80)
                    ->searchable(),
                TextColumn::make('question_type')
                    ->label('Tipe')
                    ->badge()
                    ->sortable(),
                TextColumn::make('points')
                    ->label('Poin')
                    ->sortable(),
            ])
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
