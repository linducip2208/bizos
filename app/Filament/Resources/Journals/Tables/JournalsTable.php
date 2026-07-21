<?php

namespace App\Filament\Resources\Journals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class JournalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('journal_number')
                    ->label('Nomor Jurnal')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('journal_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('journal_type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'general' => 'Umum',
                        'sales' => 'Penjualan',
                        'purchase' => 'Pembelian',
                        'cash_receipt' => 'Penerimaan Kas',
                        'cash_payment' => 'Pengeluaran Kas',
                        'adjustment' => 'Penyesuaian',
                        'opening' => 'Saldo Awal',
                        'closing' => 'Tutup Buku',
                        'depreciation' => 'Penyusutan',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'general' => 'gray',
                        'sales' => 'success',
                        'purchase' => 'warning',
                        'cash_receipt' => 'info',
                        'cash_payment' => 'danger',
                        'adjustment' => 'info',
                        'opening' => 'primary',
                        'closing' => 'danger',
                        'depreciation' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('total_debit')
                    ->label('Total Debit')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('total_credit')
                    ->label('Total Kredit')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'draft' => 'Draft',
                        'posted' => 'Posted',
                        'void' => 'Void',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'draft' => 'gray',
                        'posted' => 'success',
                        'void' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('journal_date', 'desc')
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
