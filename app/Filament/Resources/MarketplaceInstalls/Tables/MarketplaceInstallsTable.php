<?php

namespace App\Filament\Resources\MarketplaceInstalls\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MarketplaceInstallsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('app.name')
                    ->label('Aplikasi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('company.name')
                    ->label('Perusahaan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('installed_version')
                    ->label('Versi Terinstall')
                    ->badge()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'active' => 'Aktif',
                        'pending_payment' => 'Menunggu Bayar',
                        'suspended' => 'Ditangguhkan',
                        'uninstalled' => 'Dihapus',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'pending_payment' => 'warning',
                        'suspended' => 'danger',
                        'uninstalled' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('subscription_end')
                    ->label('Langganan Sampai')
                    ->date()
                    ->sortable(),
                TextColumn::make('last_checked_at')
                    ->label('Terakhir Dicek')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Tanggal Install')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktif',
                        'pending_payment' => 'Menunggu Bayar',
                        'suspended' => 'Ditangguhkan',
                        'uninstalled' => 'Dihapus',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Install'),
                ]),
            ]);
    }
}