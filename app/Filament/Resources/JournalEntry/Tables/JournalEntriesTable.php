<?php

namespace App\Filament\Resources\JournalEntry\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class JournalEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('journal.journal_number')
                    ->label('Nomor Jurnal')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('coa.name')
                    ->label('Akun')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->searchable(),
                TextColumn::make('debit')
                    ->label('Debit')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('credit')
                    ->label('Kredit')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->defaultSort('journal_id', 'desc')
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