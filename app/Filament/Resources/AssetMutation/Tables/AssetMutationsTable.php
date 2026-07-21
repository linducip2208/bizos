<?php

namespace App\Filament\Resources\AssetMutation\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssetMutationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('asset.name')
                    ->label('Aset')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mutation_type')
                    ->label('Tipe Mutasi')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('from_location')
                    ->label('Dari Lokasi')
                    ->searchable(),
                TextColumn::make('to_location')
                    ->label('Ke Lokasi')
                    ->searchable(),
                TextColumn::make('fromEmployee.name')
                    ->label('Dari Karyawan')
                    ->searchable(),
                TextColumn::make('toEmployee.name')
                    ->label('Ke Karyawan')
                    ->searchable(),
                TextColumn::make('mutation_date')
                    ->label('Tgl. Mutasi')
                    ->date('d M Y')
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