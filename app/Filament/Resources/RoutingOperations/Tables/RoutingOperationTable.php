<?php

namespace App\Filament\Resources\RoutingOperations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RoutingOperationTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('operation_name')
                    ->label('Nama Operasi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sequence')
                    ->label('Urutan')
                    ->sortable(),
                TextColumn::make('workCenter.name')
                    ->label('Work Center')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('setup_time_minutes')
                    ->label('Setup (menit)')
                    ->sortable(),
                TextColumn::make('run_time_minutes_per_unit')
                    ->label('Proses/Unit (menit)')
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