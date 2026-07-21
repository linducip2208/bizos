<?php

namespace App\Filament\Resources\Subscriptions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label('Perusahaan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('plan.name')
                    ->label('Paket')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'trial' => 'info',
                        'grace' => 'warning',
                        'expired' => 'danger',
                        'cancelled' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($record) => $record->statusLabel()),
                ToggleColumn::make('auto_renew')
                    ->label('Auto Renew'),
                TextColumn::make('started_at')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label('Berakhir')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('trial_ends_at')
                    ->label('Trial Berakhir')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'trial' => 'Trial',
                        'active' => 'Aktif',
                        'grace' => 'Grace Period',
                        'expired' => 'Kedaluwarsa',
                        'cancelled' => 'Dibatalkan',
                    ]),
                SelectFilter::make('plan_id')
                    ->label('Paket')
                    ->relationship('plan', 'name'),
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
