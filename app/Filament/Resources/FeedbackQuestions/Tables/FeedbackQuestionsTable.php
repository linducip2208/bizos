<?php

namespace App\Filament\Resources\FeedbackQuestions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FeedbackQuestionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cycle.name')
                    ->label('Siklus')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('question')
                    ->label('Pertanyaan')
                    ->searchable()
                    ->limit(80),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'performance' => 'Performa',
                        'competency' => 'Kompetensi',
                        'behavior' => 'Perilaku',
                        'goal' => 'Target',
                        'development' => 'Pengembangan',
                        'general' => 'Umum',
                        default => $state,
                    }),
                TextColumn::make('question_type')
                    ->label('Tipe')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'rating_scale' => 'Skala Rating',
                        'text' => 'Teks',
                        'multiple_choice' => 'Pilihan Ganda',
                        'yes_no' => 'Ya/Tidak',
                        default => $state,
                    }),
            ])
            ->defaultSort('sort_order', 'asc')
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
