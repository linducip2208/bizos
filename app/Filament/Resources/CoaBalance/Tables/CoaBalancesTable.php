<?php

namespace App\Filament\Resources\CoaBalance\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CoaBalancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('coa.name')
                    ->label('Akun')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('year')
                    ->label('Tahun')
                    ->sortable()
                    ->numeric(),
                TextColumn::make('month')
                    ->label('Bulan')
                    ->sortable()
                    ->numeric(),
                TextColumn::make('debit_total')
                    ->label('Total Debit')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('credit_total')
                    ->label('Total Kredit')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('closing_balance')
                    ->label('Saldo Akhir')
                    ->numeric()
                    ->sortable(),
            ])
            ->defaultSort('year', 'desc')
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
