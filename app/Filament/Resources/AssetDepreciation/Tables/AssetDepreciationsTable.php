<?php

namespace App\Filament\Resources\AssetDepreciation\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssetDepreciationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('asset.name')
                    ->label('Aset')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('year')
                    ->label('Tahun')
                    ->sortable()
                    ->numeric(),
                TextColumn::make('month')
                    ->label('Bulan')
                    ->sortable()
                    ->numeric(),
                TextColumn::make('depreciation_amount')
                    ->label('Penyusutan')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('book_value_after')
                    ->label('Nilai Buku')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->defaultSort('year', 'desc')
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
