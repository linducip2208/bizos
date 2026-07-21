<?php

namespace App\Filament\Resources\FeedbackReviewers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FeedbackReviewersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cycle.name')
                    ->label('Siklus')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('reviewee.first_name')
                    ->label('Karyawan Dinilai')
                    ->state(fn ($record) => trim(($record->reviewee?->first_name ?? '') . ' ' . ($record->reviewee?->last_name ?? '')))
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                TextColumn::make('reviewer.first_name')
                    ->label('Reviewer')
                    ->state(fn ($record) => trim(($record->reviewer?->first_name ?? '') . ' ' . ($record->reviewer?->last_name ?? '')))
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                TextColumn::make('reviewer_type')
                    ->label('Tipe')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'self' => 'Diri Sendiri',
                        'peer' => 'Rekan Kerja',
                        'manager' => 'Atasan',
                        'subordinate' => 'Bawahan',
                        default => $state,
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'Pending',
                        'in_progress' => 'Sedang Diisi',
                        'completed' => 'Selesai',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'pending' => 'warning',
                        'in_progress' => 'info',
                        'completed' => 'success',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('cycle_id', 'asc')
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