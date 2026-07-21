<?php

namespace App\Filament\Resources\BomItemResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BomItemTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('bom.name')
                    ->label('BOM')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Komponen')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity_per_unit')
                    ->label('Qty/Unit')
                    ->sortable(),
                TextColumn::make('unit')
                    ->label('Satuan'),
                TextColumn::make('scrap_percent')
                    ->label('Scrap (%)')
                    ->suffix('%')
                    ->sortable(),
                IconColumn::make('is_critical')
                    ->label('Kritis')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('Urutan')
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
