<?php

namespace App\Filament\Resources\PropertyUnits\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PropertyUnitTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('unit_number')
                    ->label('Nomor Unit')
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('property_type')
                    ->label('Tipe')
                    ->colors([
                        'info' => 'apartment',
                        'success' => 'house',
                        'warning' => 'shop',
                        'primary' => 'office',
                        'gray' => 'warehouse',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'apartment' => 'Apartemen',
                        'house' => 'Rumah',
                        'shop' => 'Ruko',
                        'office' => 'Kantor',
                        'warehouse' => 'Gudang',
                        default => $state,
                    }),
                TextColumn::make('building_name')
                    ->label('Gedung')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('land_area_sqm')
                    ->label('Luas Tanah')
                    ->suffix(' m2')
                    ->numeric(1)
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('building_area_sqm')
                    ->label('Luas Bangunan')
                    ->suffix(' m2')
                    ->numeric(1)
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('bedrooms')
                    ->label('KT')
                    ->suffix(' KM')
                    ->formatStateUsing(fn ($record) => "{$record->bedrooms} KT / {$record->bathrooms} KM")
                    ->sortable(),
                TextColumn::make('purchase_price')
                    ->label('Harga Beli')
                    ->money('IDR')
                    ->sortable()
                    ->placeholder('-'),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'available',
                        'primary' => 'rented',
                        'info' => 'sold',
                        'warning' => 'maintenance',
                        'danger' => 'vacant',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'available' => 'Tersedia',
                        'rented' => 'Disewakan',
                        'sold' => 'Terjual',
                        'maintenance' => 'Perbaikan',
                        'vacant' => 'Kosong',
                        default => $state,
                    }),
            ])
            ->defaultSort('unit_number', 'asc')
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
