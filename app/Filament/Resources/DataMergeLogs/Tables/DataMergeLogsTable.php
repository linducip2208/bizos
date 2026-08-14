<?php

namespace App\Filament\Resources\DataMergeLogs\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DataMergeLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('entity_type')
                    ->label('Tipe Entitas')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'client' => 'blue',
                        'supplier' => 'violet',
                        'product' => 'teal',
                        'employee' => 'orange',
                        'lead' => 'pink',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'client' => 'Klien',
                        'supplier' => 'Pemasok',
                        'product' => 'Produk',
                        'employee' => 'Karyawan',
                        'lead' => 'Prospek',
                        default => $state,
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('target_id')
                    ->label('ID Target')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('source_ids')
                    ->label('ID Sumber')
                    ->formatStateUsing(fn($state) => is_array($state) ? implode(', ', $state) : $state)
                    ->searchable(),

                TextColumn::make('merger.name')
                    ->label('Digabungkan Oleh')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
