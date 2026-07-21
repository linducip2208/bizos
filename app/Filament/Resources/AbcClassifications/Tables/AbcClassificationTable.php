<?php

namespace App\Filament\Resources\AbcClassifications\Tables;

use App\Models\AbcClassification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AbcClassificationTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.code')
                    ->label('Kode Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('classification')
                    ->label('Klasifikasi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'A' => 'danger',
                        'B' => 'warning',
                        'C' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('annual_consumption_value')
                    ->label('Nilai Konsumsi Tahunan')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('cumulative_percent')
                    ->label('Persentase Kumulatif')
                    ->suffix('%')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
                TextColumn::make('calculated_at')
                    ->label('Dihitung Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('classification')
                    ->label('Klasifikasi')
                    ->options([
                        'A' => 'A (70%)',
                        'B' => 'B (20%)',
                        'C' => 'C (10%)',
                    ]),
                SelectFilter::make('product')
                    ->label('Produk')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('cumulative_percent', 'asc');
    }
}