<?php

namespace App\Filament\Resources\RewardResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RewardTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Gambar')
                    ->circular()
                    ->defaultImageUrl(fn() => url('/images/placeholder-reward.png')),
                TextColumn::make('name')
                    ->label('Nama Reward')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->sortable(),
                TextColumn::make('points_cost')
                    ->label('Biaya Poin')
                    ->sortable()
                    ->formatStateUsing(fn($state) => number_format($state) . ' pts'),
                TextColumn::make('stock')
                    ->label('Stok')
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state <= 0 ? ($state === -1 ? 'Unlimited' : 'Habis') : (string) $state),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
            ])
            ->defaultSort('points_cost')
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
