<?php

namespace App\Filament\Resources\CustomerGroups\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomerGroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('priceList.name')
                    ->label('Daftar Harga')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('discount_percent')
                    ->label('Diskon (%)')
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format((float) $state, 2) . '%' : '-')
                    ->sortable(),
                TextColumn::make('members_count')
                    ->label('Jumlah Member')
                    ->counts('members'),
                TextColumn::make('clients_count')
                    ->label('Jumlah Klien')
                    ->counts('clients'),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
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
