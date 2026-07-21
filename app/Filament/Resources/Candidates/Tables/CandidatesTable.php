<?php

namespace App\Filament\Resources\Candidates\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CandidatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name')
                    ->label('Nama Lengkap')
                    ->state(fn ($record) => trim(($record->first_name ?? '') . ' ' . ($record->last_name ?? '')))
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('Telepon')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('jobPosting.title')
                    ->label('Lowongan')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('pipeline_stage')
                    ->label('Tahapan')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'applied' => 'Melamar',
                        'screening' => 'Screening',
                        'interview' => 'Interview',
                        'technical_test' => 'Test Teknis',
                        'offering' => 'Penawaran',
                        'hired' => 'Diterima',
                        'rejected' => 'Ditolak',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'applied' => 'gray',
                        'screening' => 'info',
                        'interview' => 'warning',
                        'technical_test' => 'warning',
                        'offering' => 'success',
                        'hired' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('created_at', 'desc')
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