<?php

namespace App\Filament\Resources\RabItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RabItemTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('item_code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('project.name')
                    ->label('Proyek')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('unit')
                    ->label('Satuan')
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Volume')
                    ->numeric(2)
                    ->sortable(),
                TextColumn::make('unit_price')
                    ->label('Harga Satuan')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),
                BadgeColumn::make('category')
                    ->label('Kategori')
                    ->colors([
                        'primary' => 'material',
                        'success' => 'labor',
                        'warning' => 'equipment',
                        'danger' => 'subcontract',
                        'gray' => 'overhead',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'material' => 'Material',
                        'labor' => 'Tenaga Kerja',
                        'equipment' => 'Alat Berat',
                        'subcontract' => 'Subkontraktor',
                        'overhead' => 'Overhead',
                        default => $state,
                    }),
                TextColumn::make('weight_percent')
                    ->label('Bobot %')
                    ->suffix('%')
                    ->sortable()
                    ->toggleable(),

            ])
            ->defaultSort('sort_order', 'asc')
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