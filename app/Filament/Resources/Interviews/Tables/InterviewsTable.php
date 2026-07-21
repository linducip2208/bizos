<?php

namespace App\Filament\Resources\Interviews\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InterviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('candidate.first_name')
                    ->label('Kandidat')
                    ->state(fn ($record) => trim(($record->candidate?->first_name ?? '') . ' ' . ($record->candidate?->last_name ?? '')))
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                TextColumn::make('interview_type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'phone' => 'Telepon',
                        'video' => 'Video',
                        'onsite' => 'Onsite',
                        'technical_test' => 'Tes Teknis',
                        'final' => 'Final',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'phone' => 'info',
                        'video' => 'success',
                        'onsite' => 'warning',
                        'technical_test' => 'danger',
                        'final' => 'primary',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('scheduled_at')
                    ->label('Jadwal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('duration_minutes')
                    ->label('Durasi (Menit)')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'scheduled' => 'Terjadwal',
                        'in_progress' => 'Sedang Berlangsung',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        'no_show' => 'Tidak Hadir',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'scheduled' => 'info',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'gray',
                        'no_show' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('scheduled_at', 'desc')
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
