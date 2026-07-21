<?php

namespace App\Filament\Resources\IsoAudits\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IsoAuditTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('planned_date', 'desc')
            ->columns([
                TextColumn::make('audit_number')
                    ->label('No')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->limit(35),
                TextColumn::make('audit_type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'internal' => 'Internal',
                        'external' => 'Eksternal',
                        'surveillance' => 'Surveillance',
                        'certification' => 'Sertifikasi',
                        'recertification' => 'Re-Sertifikasi',
                        default => $state,
                    }),
                TextColumn::make('scope')
                    ->label('Lingkup')
                    ->limit(30),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'completed' => 'success',
                        'in_progress' => 'warning',
                        'planned' => 'info',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('result')
                    ->label('Hasil')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'pass' => 'success',
                        'pass_with_observation' => 'warning',
                        'fail' => 'danger',
                        'pending' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('planned_date')
                    ->label('Rencana')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('leadAuditor.name')
                    ->label('Lead Auditor'),
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