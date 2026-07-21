<?php

namespace App\Filament\Resources\IsoRisks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IsoRiskTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('risk_score', 'desc')
            ->columns([
                TextColumn::make('asset_name')
                    ->label('Aset')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('asset_type')
                    ->label('Tipe')
                    ->badge(),
                TextColumn::make('threat')
                    ->label('Ancaman')
                    ->limit(30)
                    ->searchable(),
                TextColumn::make('risk_score')
                    ->label('Skor')
                    ->sortable()
                    ->alignRight(),
                TextColumn::make('risk_level')
                    ->label('Level')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'critical' => 'danger',
                        'high' => 'warning',
                        'medium' => 'info',
                        'low' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('treatment')
                    ->label('Perlakuan')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'accept' => 'Terima',
                        'mitigate' => 'Mitigasi',
                        'transfer' => 'Transfer',
                        'avoid' => 'Hindari',
                        default => $state,
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('review_due')
                    ->label('Review')
                    ->date('d M Y')
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
