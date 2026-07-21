<?php

namespace App\Filament\Resources\EsgTargets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EsgTargetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('metric_label')
                    ->label('Metrik')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'environmental' => 'Lingkungan',
                        'social' => 'Sosial',
                        'governance' => 'Tata Kelola',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'environmental' => 'success',
                        'social' => 'info',
                        'governance' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('target_value')
                    ->label('Target')
                    ->numeric(2)
                    ->suffix(fn($r) => ' ' . $r->unit)
                    ->sortable(),
                TextColumn::make('current_value')
                    ->label('Saat Ini')
                    ->numeric(2)
                    ->suffix(fn($r) => ' ' . $r->unit)
                    ->sortable(),
                TextColumn::make('progress_percent')
                    ->label('Progress')
                    ->formatStateUsing(fn($r) => round($r->progress_percent, 1) . '%')
                    ->sortable(),
                TextColumn::make('deadline')
                    ->label('Tenggat')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'on_track' => 'Sesuai Jalur',
                        'at_risk' => 'Berisiko',
                        'behind' => 'Tertinggal',
                        'achieved' => 'Tercapai',
                        'abandoned' => 'Dibatalkan',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'on_track' => 'success',
                        'at_risk' => 'warning',
                        'behind' => 'danger',
                        'achieved' => 'success',
                        'abandoned' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('responsible_person')
                    ->label('PJ')
                    ->searchable(),
            ])
            ->defaultSort('deadline', 'asc')
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
