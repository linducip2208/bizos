<?php

namespace App\Filament\Resources\VirtualAccounts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VirtualAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('va_number')
                    ->label('Nomor VA')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('bank')
                    ->label('Bank')
                    ->formatStateUsing(fn($state) => strtoupper($state))
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'bca' => 'info',
                        'mandiri' => 'warning',
                        'bri' => 'danger',
                        'bni' => 'success',
                        'cimb' => 'indigo',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('expected_amount')
                    ->label('Diharapkan')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('paid_amount')
                    ->label('Dibayar')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'pending' => 'Menunggu',
                        'active' => 'Aktif',
                        'paid' => 'Sudah Dibayar',
                        'expired' => 'Kadaluarsa',
                        'closed' => 'Ditutup',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'pending' => 'warning',
                        'active' => 'success',
                        'paid' => 'success',
                        'expired' => 'danger',
                        'closed' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('expiry_at')
                    ->label('Kadaluarsa')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
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
