<?php

namespace App\Filament\Resources\IntegrationConnectors\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IntegrationConnectorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('connector_type')
                    ->label('Jenis')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'jurnal_id' => 'Jurnal.id',
                        'xero' => 'Xero',
                        'accurate' => 'Accurate Online',
                        'google_workspace' => 'Google Workspace',
                        'microsoft_365' => 'Microsoft 365',
                        'open_banking' => 'Open Banking',
                        'djp' => 'DJP (Pajak)',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'jurnal_id' => 'blue',
                        'xero' => 'cyan',
                        'accurate' => 'emerald',
                        'google_workspace' => 'red',
                        'microsoft_365' => 'indigo',
                        'open_banking' => 'amber',
                        'djp' => 'orange',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'disconnected' => 'Terputus',
                        'connecting' => 'Menghubungkan',
                        'connected' => 'Terhubung',
                        'error' => 'Error',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'connected' => 'success',
                        'connecting' => 'warning',
                        'disconnected' => 'gray',
                        'error' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('last_sync_at')
                    ->label('Sync Terakhir')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('is_active')
                    ->label('Aktif')
                    ->formatStateUsing(fn($state) => $state ? 'Ya' : 'Tidak')
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'gray'),
            ])
            ->defaultSort('name', 'asc')
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
