<?php

namespace App\Filament\Resources\PayrollPeriod\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PayrollPeriodsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label('Perusahaan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('period_code')
                    ->label('Kode Periode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label('Tgl. Mulai')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Tgl. Selesai')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('payment_date')
                    ->label('Tgl. Pembayaran')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_gross')
                    ->label('Total Bruto')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('total_net')
                    ->label('Total Netto')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('total_employees')
                    ->label('Jml. Karyawan')
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
