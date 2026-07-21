<?php

namespace App\Filament\Resources\PosRefunds\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PosRefundsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('refund_number')
                    ->label('No. Refund')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('transaction.receipt_number')
                    ->label('No. Struk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('refund_date')
                    ->label('Tanggal Refund')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('refundedBy.first_name')
                    ->label('Direfund Oleh')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
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