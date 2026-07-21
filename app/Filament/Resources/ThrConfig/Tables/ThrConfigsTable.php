<?php

namespace App\Filament\Resources\ThrConfig\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ThrConfigsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label('Perusahaan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('religious_holiday')
                    ->label('Hari Raya')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('min_months_service')
                    ->label('Min. Bulan Kerja')
                    ->sortable(),
                TextColumn::make('formula')
                    ->label('Formula')
                    ->badge()
                    ->sortable(),
                TextColumn::make('payment_deadline_days')
                    ->label('Batas Bayar (hari)')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
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
