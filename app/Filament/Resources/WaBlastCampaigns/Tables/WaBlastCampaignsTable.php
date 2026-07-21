<?php

namespace App\Filament\Resources\WaBlastCampaigns\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WaBlastCampaignsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('template.name')
                    ->label('Template')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('target_type')
                    ->label('Tipe Target')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'segment' => 'Segment',
                        'manual' => 'Manual',
                        default => $state,
                    }),
                TextColumn::make('scheduled_at')
                    ->label('Jadwal Kirim')
                    ->date('d M Y H:i')
                    ->sortable(),
                TextColumn::make('total_sent')
                    ->label('Terkirim')
                    ->sortable(),
                TextColumn::make('total_targets')
                    ->label('Total Target')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'terjadwal' => 'info',
                        'dikirim' => 'warning',
                        'selesai' => 'success',
                        'dibatalkan' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'terjadwal' => 'Terjadwal',
                        'dikirim' => 'Dikirim',
                        'selesai' => 'Selesai',
                        'dibatalkan' => 'Dibatalkan',
                        default => $state,
                    }),
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