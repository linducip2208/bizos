<?php

namespace App\Filament\Resources\Forecasts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ForecastsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label('Perusahaan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama Forecast')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('forecast_type')
                    ->label('Tipe')
                    ->badge()
                    ->sortable(),
                TextColumn::make('fiscal_year')
                    ->label('Tahun Fiskal')
                    ->sortable(),
                TextColumn::make('frequency')
                    ->label('Frekuensi')
                    ->badge()
                    ->sortable(),
                TextColumn::make('version')
                    ->label('Versi')
                    ->sortable(),
                IconColumn::make('is_rolling')
                    ->label('Rolling')
                    ->boolean(),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
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
