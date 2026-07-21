<?php

namespace App\Filament\Resources\BudgetItem\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BudgetItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('budget.name')
                    ->label('Anggaran')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('coa.name')
                    ->label('Akun')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->searchable(),
                TextColumn::make('planned_amount')
                    ->label('Rencana')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('actual_amount')
                    ->label('Aktual')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('variance')
                    ->label('Varians')
                    ->numeric()
                    ->placeholder('-')
                    ->sortable(),
            ])
            ->defaultSort('budget_id', 'desc')
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
