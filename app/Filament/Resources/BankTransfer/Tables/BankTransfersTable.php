<?php

namespace App\Filament\Resources\BankTransfer\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BankTransfersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transfer_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('fromAccount.bank_name')
                    ->label('Dari Bank')
                    ->sortable(),
                TextColumn::make('fromAccount.account_name')
                    ->label('Dari Rekening')
                    ->limit(20),
                TextColumn::make('toAccount.bank_name')
                    ->label('Ke Bank')
                    ->sortable(),
                TextColumn::make('toAccount.account_name')
                    ->label('Ke Rekening')
                    ->limit(20),
                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('exchange_rate')
                    ->label('Kurs')
                    ->numeric(6)
                    ->placeholder('-'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'draft' => 'Draft',
                        'completed' => 'Selesai',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'draft' => 'gray',
                        'completed' => 'success',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('transfer_date', 'desc')
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