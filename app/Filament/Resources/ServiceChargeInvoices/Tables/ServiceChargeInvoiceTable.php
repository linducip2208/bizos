<?php

namespace App\Filament\Resources\ServiceChargeInvoices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServiceChargeInvoiceTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Invoice')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('propertyUnit.unit_number')
                    ->label('Unit')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tenancyContract.client.name')
                    ->label('Penyewa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('period_start')
                    ->label('Periode Mulai')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('period_end')
                    ->label('Periode Selesai')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('rent_amount')
                    ->label('Sewa')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('service_charge')
                    ->label('SC')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')
                    ->sortable(),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'danger' => 'unpaid',
                        'success' => 'paid',
                        'warning' => 'overdue',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'unpaid' => 'Belum Dibayar',
                        'paid' => 'Dibayar',
                        'overdue' => 'Terlambat',
                        default => $state,
                    }),
            ])
            ->defaultSort('due_date', 'desc')
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
