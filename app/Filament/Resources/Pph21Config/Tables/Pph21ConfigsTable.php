<?php

namespace App\Filament\Resources\Pph21Config\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class Pph21ConfigsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label('Perusahaan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ptkp_category')
                    ->label('Kategori PTKP')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ptkp_amount')
                    ->label('PTKP (Rp)')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('effective_year')
                    ->label('Tahun Berlaku')
                    ->sortable()
                    ->numeric(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->defaultSort('effective_year', 'desc')
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