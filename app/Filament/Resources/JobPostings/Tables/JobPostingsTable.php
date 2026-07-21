<?php

namespace App\Filament\Resources\JobPostings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class JobPostingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label('Perusahaan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('department.name')
                    ->label('Departemen')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('position.name')
                    ->label('Posisi')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('employee_type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'permanent' => 'Tetap',
                        'contract' => 'Kontrak',
                        'probation' => 'Probation',
                        'intern' => 'Magang',
                        'freelance' => 'Freelance',
                        default => $state,
                    }),
                TextColumn::make('quota')
                    ->label('Kuota')
                    ->sortable()
                    ->numeric(),
                IconColumn::make('is_remote')
                    ->label('Remote')
                    ->boolean(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'published' => 'success',
                        'closed' => 'danger',
                        'cancelled' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'published' => 'Dipublikasi',
                        'closed' => 'Ditutup',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    }),
                TextColumn::make('location')
                    ->label('Lokasi')
                    ->searchable()
                    ->placeholder('-'),
            ])
            ->defaultSort('title', 'asc')
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