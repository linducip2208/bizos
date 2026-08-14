<?php

namespace App\Filament\Resources\ModifierGroups\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ModifierGroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Grup')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('selection_type')
                    ->label('Tipe Pilihan')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state === 'multiple' ? 'Pilih Banyak' : 'Pilih Satu')
                    ->color(fn (?string $state): string => $state === 'multiple' ? 'info' : 'gray'),
                TextColumn::make('modifiers_count')
                    ->label('Jumlah Modifier')
                    ->counts('modifiers')
                    ->sortable(),
                IconColumn::make('is_required')
                    ->label('Wajib')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                //
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
