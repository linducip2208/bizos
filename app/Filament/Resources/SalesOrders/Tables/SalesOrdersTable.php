<?php

namespace App\Filament\Resources\SalesOrders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SalesOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('so_number')
                    ->label('No. SO')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('client.name')
                    ->label('Klien')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('order_date')
                    ->label('Tgl Order')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('expected_delivery')
                    ->label('Estimasi Kirim')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray', 'confirmed' => 'info', 'in_progress' => 'warning',
                        'shipped' => 'primary', 'delivered' => 'success', 'invoiced' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'draft' => 'Draft', 'confirmed' => 'Dikonfirmasi', 'in_progress' => 'Diproses',
                        'shipped' => 'Dikirim', 'delivered' => 'Terkirim', 'invoiced' => 'Tertagih',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    }),
                TextColumn::make('createdBy.first_name')
                    ->label('Dibuat Oleh'),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
