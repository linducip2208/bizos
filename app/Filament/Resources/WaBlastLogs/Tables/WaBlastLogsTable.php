<?php

namespace App\Filament\Resources\WaBlastLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WaBlastLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('campaign.name')
                    ->label('Kampanye')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contact_name')
                    ->label('Nama Kontak')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contact_phone')
                    ->label('No. Telepon')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'tertunda' => 'gray',
                        'terkirim' => 'info',
                        'terkirim_wa' => 'success',
                        'dibaca' => 'success',
                        'gagal' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'tertunda' => 'Tertunda',
                        'terkirim' => 'Terkirim',
                        'terkirim_wa' => 'Terkirim WA',
                        'dibaca' => 'Dibaca',
                        'gagal' => 'Gagal',
                        default => $state,
                    }),
                TextColumn::make('sent_at')
                    ->label('Terkirim')
                    ->date('d M Y H:i')
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