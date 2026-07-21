<?php

namespace App\Filament\Resources\ExchangeRateLogResource\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExchangeRateLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('currency.code')
                    ->label('Mata Uang')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('currency.name')
                    ->label('Nama')
                    ->sortable(),
                TextColumn::make('rate_date')
                    ->label('Tanggal Kurs')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('rate')
                    ->label('Nilai Tukar')
                    ->numeric(6)
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Dicatat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('rate_date', 'desc');
    }
}
