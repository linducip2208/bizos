<?php

namespace App\Filament\Resources\ReconciliationItemResource\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReconciliationItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reconciliation.id')
                    ->label('Rekonsiliasi #')
                    ->sortable(),
                TextColumn::make('reconciliation.bankAccount.bank_name')
                    ->label('Bank')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'matched' => 'Cocok',
                        'unmatched_journal' => 'Jurnal Tidak Cocok',
                        'unmatched_bank' => 'Bank Tidak Cocok',
                        'adjustment' => 'Penyesuaian',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'matched' => 'success',
                        'unmatched_journal' => 'warning',
                        'unmatched_bank' => 'danger',
                        'adjustment' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('matched_amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('journalEntry.coa.code')
                    ->label('COA')
                    ->sortable(),
                TextColumn::make('bankTransaction.description')
                    ->label('Deskripsi Bank')
                    ->limit(35),
                TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(40),
            ])
            ->defaultSort('id', 'desc');
    }
}
