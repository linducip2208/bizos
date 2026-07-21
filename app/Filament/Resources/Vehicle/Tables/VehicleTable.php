<?php

namespace App\Filament\Resources\Vehicle\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;

class VehicleTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('plate_number')
                    ->label('Nomor Plat')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('brand')
                    ->label('Merek')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('model')
                    ->label('Model')
                    ->searchable(),
                TextColumn::make('year')
                    ->label('Tahun')
                    ->sortable(),
                TextColumn::make('vehicle_type')
                    ->label('Tipe')
                    ->formatStateUsing(fn($state) => match($state) {
                        'car' => 'Mobil', 'motorcycle' => 'Motor', 'truck' => 'Truk', default => $state,
                    })
                    ->badge(),
                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn($state) => match($state) {
                        'available' => 'Tersedia', 'in_use' => 'Digunakan',
                        'maintenance' => 'Perawatan', 'sold' => 'Terjual', default => $state,
                    })
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'available' => 'success', 'in_use' => 'primary',
                        'maintenance' => 'warning', 'sold' => 'danger', default => 'gray',
                    }),
                TextColumn::make('last_odometer')
                    ->label('Odometer')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ownership')
                    ->label('Kepemilikan')
                    ->formatStateUsing(fn($state) => $state === 'company' ? 'Perusahaan' : 'Sewa'),
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