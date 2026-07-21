<?php

namespace App\Filament\Resources\LoyaltyConfigs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LoyaltyConfigTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label('Perusahaan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('earn_rate')
                    ->label('Perolehan Poin')
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('redeem_rate')
                    ->label('Penukaran (Rp/poin)')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('points_expiry_months')
                    ->label('Kadaluarsa (bln)')
                    ->sortable(),
                TextColumn::make('silver_threshold')
                    ->label('Silver')
                    ->sortable(),
                TextColumn::make('gold_threshold')
                    ->label('Gold')
                    ->sortable(),
                TextColumn::make('platinum_threshold')
                    ->label('Platinum')
                    ->sortable(),
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
