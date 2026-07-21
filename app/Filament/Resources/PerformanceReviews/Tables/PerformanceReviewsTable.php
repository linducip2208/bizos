<?php

namespace App\Filament\Resources\PerformanceReviews\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PerformanceReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cycle.name')
                    ->label('Siklus')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.first_name')
                    ->label('Karyawan')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($record) => $record->employee?->first_name . ' ' . ($record->employee?->last_name ?? '')),
                TextColumn::make('reviewer.first_name')
                    ->label('Penilai')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($record) => $record->reviewer?->first_name . ' ' . ($record->reviewer?->last_name ?? '')),
                TextColumn::make('final_score')
                    ->label('Skor Akhir')
                    ->numeric(2)
                    ->sortable(),
                TextColumn::make('rating')
                    ->label('Rating')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'A' => 'success',
                        'B' => 'primary',
                        'C' => 'warning',
                        'D' => 'danger',
                        'E' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'self_assessment' => 'warning',
                        'manager_review' => 'info',
                        'hr_calibration' => 'primary',
                        'completed' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'self_assessment' => 'Self Assessment',
                        'manager_review' => 'Review Manager',
                        'hr_calibration' => 'Kalibrasi HR',
                        'completed' => 'Selesai',
                        default => $state,
                    }),
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
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
