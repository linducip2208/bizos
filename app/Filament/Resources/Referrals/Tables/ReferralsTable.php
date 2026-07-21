<?php

namespace App\Filament\Resources\Referrals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReferralsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('referrerClient.name')
                    ->label('Klien Pereferensi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('referred_name')
                    ->label('Nama Direferensi')
                    ->searchable(),
                TextColumn::make('referred_phone')
                    ->label('Telepon')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'gray', 'signed_up' => 'info', 'converted' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Pending', 'signed_up' => 'Mendaftar', 'converted' => 'Terkonversi',
                        default => $state,
                    }),
                TextColumn::make('reward_status')
                    ->label('Reward')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'gray', 'earned' => 'warning', 'paid' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
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
