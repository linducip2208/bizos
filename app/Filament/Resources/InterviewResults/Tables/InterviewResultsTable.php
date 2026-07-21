<?php

namespace App\Filament\Resources\InterviewResults\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InterviewResultsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('interview.candidate.first_name')
                    ->label('Kandidat')
                    ->state(fn ($record) => trim(($record->interview?->candidate?->first_name ?? '') . ' ' . ($record->interview?->candidate?->last_name ?? '')))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('interviewer.employee.first_name')
                    ->label('Pewawancara')
                    ->state(fn ($record) => trim(($record->interviewer?->employee?->first_name ?? '') . ' ' . ($record->interviewer?->employee?->last_name ?? '')))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('rating')
                    ->label('Rating')
                    ->sortable()
                    ->numeric(),
                TextColumn::make('recommendation')
                    ->label('Rekomendasi')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'strong_hire' => 'Sangat Direkomendasikan',
                        'hire' => 'Direkomendasikan',
                        'consider' => 'Dipertimbangkan',
                        'not_recommended' => 'Tidak Direkomendasikan',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'strong_hire' => 'success',
                        'hire' => 'primary',
                        'consider' => 'warning',
                        'not_recommended' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('rating', 'desc')
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
