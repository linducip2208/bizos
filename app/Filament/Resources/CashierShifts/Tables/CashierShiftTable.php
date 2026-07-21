<?php

namespace App\Filament\Resources\CashierShifts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CashierShiftTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.first_name')
                    ->label('Kasir')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('shift_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('opening_balance')
                    ->label('Saldo Awal')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('closing_balance')
                    ->label('Saldo Akhir')
                    ->money('IDR')
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('actual_cash')
                    ->label('Kas Aktual')
                    ->money('IDR')
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('difference')
                    ->label('Selisih')
                    ->money('IDR')
                    ->sortable()
                    ->placeholder('-')
                    ->color(fn ($state) => $state != 0 ? 'danger' : 'success'),
                TextColumn::make('total_transactions')
                    ->label('Trx')
                    ->sortable(),
                TextColumn::make('total_sales')
                    ->label('Total Penjualan')
                    ->money('IDR')
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'open' => 'Buka',
                        'closed' => 'Tutup',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'open' => 'warning',
                        'closed' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('opening_time')
                    ->label('Waktu Buka')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
