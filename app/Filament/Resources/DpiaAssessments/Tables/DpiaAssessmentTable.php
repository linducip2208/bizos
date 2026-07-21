<?php

namespace App\Filament\Resources\DpiaAssessments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DpiaAssessmentTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('processing_activity')
                    ->label('Aktivitas')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('risk_level')
                    ->label('Risiko')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'critical' => 'danger',
                        'high' => 'warning',
                        'medium' => 'info',
                        'low' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'approved' => 'success',
                        'draft' => 'gray',
                        'in_review' => 'warning',
                        'rejected' => 'danger',
                        'needs_revision' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('reviewer.name')
                    ->label('Reviewer'),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
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