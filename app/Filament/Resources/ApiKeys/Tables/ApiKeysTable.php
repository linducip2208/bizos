<?php

namespace App\Filament\Resources\ApiKeys\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ApiKeysTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('company.name')
                    ->label('Perusahaan')
                    ->sortable(),
                TextColumn::make('key')
                    ->label('Key Hash')
                    ->copyable()
                    ->formatStateUsing(fn ($state) => substr($state, 0, 16) . '...')
                    ->tooltip(fn ($record) => 'SHA256: ' . $record->key),
                TextColumn::make('rate_limit')
                    ->label('Rate Limit')
                    ->suffix(' req/mnt')
                    ->sortable(),
                TextColumn::make('permissions')
                    ->label('Izin')
                    ->badge()
                    ->formatStateUsing(fn ($state) => is_array($state) ? count($state) . ' izin' : '0 izin'),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('last_used_at')
                    ->label('Terakhir Digunakan')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Belum pernah'),
                TextColumn::make('expires_at')
                    ->label('Kedaluwarsa')
                    ->dateTime('d M Y')
                    ->placeholder('Tidak ada'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('revoke')
                    ->label('Cabut')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cabut Kunci API')
                    ->modalDescription('Kunci API akan dinonaktifkan. Semua request dengan kunci ini akan ditolak.')
                    ->action(function ($record) {
                        $record->update(['is_active' => false]);
                    })
                    ->visible(fn ($record) => $record->is_active),
                Action::make('activate')
                    ->label('Aktifkan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function ($record) {
                        $record->update(['is_active' => true]);
                    })
                    ->visible(fn ($record) => ! $record->is_active),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
