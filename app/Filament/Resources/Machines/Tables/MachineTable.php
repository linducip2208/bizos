<?php

namespace App\Filament\Resources\Machines\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MachineTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Mesin')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('model')
                    ->label('Model')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('serial_number')
                    ->label('Nomor Seri')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('workCenter.name')
                    ->label('Work Center')
                    ->sortable(),
                TextColumn::make('capacity_per_hour')
                    ->label('Kapasitas/Jam')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'active' => 'success',
                        'maintenance' => 'warning',
                        'broken' => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'active' => 'Aktif',
                        'maintenance' => 'Dalam Perawatan',
                        'broken' => 'Rusak',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('company.name')
                    ->label('Perusahaan')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->defaultSort('name', 'asc');
    }
}
