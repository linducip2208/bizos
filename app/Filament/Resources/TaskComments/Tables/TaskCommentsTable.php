<?php

namespace App\Filament\Resources\TaskComments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TaskCommentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('task.title')
                    ->label('Tugas')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.first_name')
                    ->label('Karyawan')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('comment')
                    ->label('Komentar')
                    ->limit(80)
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->date('d M Y H:i')
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