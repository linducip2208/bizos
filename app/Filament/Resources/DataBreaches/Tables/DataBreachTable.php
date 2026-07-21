<?php

namespace App\Filament\Resources\DataBreaches\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DataBreachTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('discovered_at', 'desc')
            ->columns([
                TextColumn::make('breach_type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'unauthorized_access' => 'Akses Tidak Sah',
                        'data_leak' => 'Kebocoran',
                        'malware' => 'Malware',
                        'physical_theft' => 'Fisik',
                        'insider' => 'Orang Dalam',
                        'third_party' => 'Pihak 3',
                        default => $state,
                    }),
                TextColumn::make('severity')
                    ->label('Keparahan')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'critical' => 'danger',
                        'high' => 'warning',
                        'medium' => 'info',
                        'low' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(60)
                    ->searchable(),
                TextColumn::make('affected_records_count')
                    ->label('Data Terdampak')
                    ->sortable()
                    ->alignRight(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'closed', 'resolved' => 'success',
                        'contained' => 'info',
                        'investigating' => 'warning',
                        'open' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('discovered_at')
                    ->label('Ditemukan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('notified_dpa_at')
                    ->label('Lapor DPA')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Belum'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}