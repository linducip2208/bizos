<?php

namespace App\Filament\Resources\FinishedGoods\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FinishedGoodTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('productionOrder.po_number')
                    ->label('PO Number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Kuantitas')
                    ->numeric(decimalPlaces: 4)
                    ->sortable(),
                TextColumn::make('quality_status')
                    ->label('Status Kualitas')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'passed' => 'success',
                        'failed' => 'danger',
                        'rework' => 'warning',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'passed' => 'Lolos QC',
                        'failed' => 'Gagal QC',
                        'rework' => 'Perlu Rework',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('accepted_at')
                    ->label('Diterima')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->defaultSort('created_at', 'desc');
    }
}
