<?php

namespace App\Filament\Resources\MarketplaceApps\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MarketplaceAppsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('developer')
                    ->label('Developer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('version')
                    ->label('Versi')
                    ->badge()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->sortable(),
                TextColumn::make('price_type')
                    ->label('Tipe Harga')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'free' => 'Gratis',
                        'paid' => 'Sekali Bayar',
                        'monthly' => 'Berlangganan',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'free' => 'success',
                        'paid' => 'warning',
                        'monthly' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('total_installs')
                    ->label('Total Install')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('rating')
                    ->label('Rating')
                    ->numeric(decimalPlaces: 1)
                    ->sortable(),
                IconColumn::make('is_published')
                    ->label('Publikasi')
                    ->boolean(),
                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
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