<?php

namespace App\Filament\Resources\SerialNumbers\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SerialNumberTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('serial_number')
                    ->label('Nomor Seri')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('batch.batch_number')
                    ->label('Batch')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'available' => 'success',
                        'sold' => 'primary',
                        'returned' => 'warning',
                        'damaged' => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'available' => 'Tersedia',
                        'sold' => 'Terjual',
                        'returned' => 'Dikembalikan',
                        'damaged' => 'Rusak',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('company.name')
                    ->label('Perusahaan')
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
