<?php

namespace App\Filament\Resources\Licenses\Tables;

use App\Services\LicenseService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LicensesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label('Perusahaan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('module')
                    ->label('Modul')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('seats')
                    ->label('Seat')
                    ->sortable()
                    ->numeric(),
                TextColumn::make('started_at')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->label('Kadaluarsa')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('Selamanya'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Aktif',
                        'expired' => 'Kadaluarsa',
                        'suspended' => 'Ditangguhkan',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'expired' => 'danger',
                        'suspended' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('started_at', 'desc')
            ->recordActions([
                EditAction::make(),
                Action::make('suspend')
                    ->label('Tangguhkan')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->action(fn ($record) => app(LicenseService::class)->suspendLicense($record))
                    ->hidden(fn ($record) => $record->status !== 'active')
                    ->requiresConfirmation(),
                Action::make('reactivate')
                    ->label('Aktivasi Ulang')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->action(fn ($record) => app(LicenseService::class)->reactivateLicense($record))
                    ->hidden(fn ($record) => $record->status === 'active')
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
