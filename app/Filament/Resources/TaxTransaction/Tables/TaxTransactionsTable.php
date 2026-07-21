<?php

namespace App\Filament\Resources\TaxTransaction\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TaxTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label('Perusahaan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('taxConfig.name')
                    ->label('Konfigurasi Pajak')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('reference_type')
                    ->label('Tipe Referensi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('reference_id')
                    ->label('ID Referensi')
                    ->sortable(),
                TextColumn::make('base_amount')
                    ->label('Dasar Pengenaan')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('tax_amount')
                    ->label('Jumlah Pajak')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('tax_date')
                    ->label('Tgl. Pajak')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->label('Status Bayar')
                    ->badge()
                    ->searchable()
                    ->sortable(),
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