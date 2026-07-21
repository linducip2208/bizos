<?php

namespace App\Filament\Resources\Interviewers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InterviewersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('interview.id')
                    ->label('Interview #')
                    ->sortable(),
                TextColumn::make('employee.first_name')
                    ->label('Karyawan')
                    ->state(fn ($record) => trim(($record->employee?->first_name ?? '') . ' ' . ($record->employee?->last_name ?? '')))
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                TextColumn::make('role')
                    ->label('Peran')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'lead' => 'Pewawancara Utama',
                        'technical' => 'Teknis',
                        'hr' => 'HR',
                        'observer' => 'Observer',
                        default => $state,
                    }),
            ])
            ->defaultSort('interview_id', 'asc')
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
