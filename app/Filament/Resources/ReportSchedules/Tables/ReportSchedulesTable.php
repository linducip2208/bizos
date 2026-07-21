<?php

namespace App\Filament\Resources\ReportSchedules\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ReportSchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Jadwal')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('reportTemplate.name')
                    ->label('Template')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('frequency')
                    ->label('Frekuensi')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'daily' => 'Harian',
                        'weekly' => 'Mingguan',
                        'monthly' => 'Bulanan',
                        default => ucfirst($state),
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'daily' => 'info',
                        'weekly' => 'success',
                        'monthly' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('time_of_day')
                    ->label('Waktu')
                    ->sortable(),
                TextColumn::make('format')
                    ->label('Format')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => strtoupper($state))
                    ->color(fn(string $state): string => match ($state) {
                        'pdf' => 'danger',
                        'excel' => 'success',
                        'csv' => 'info',
                        default => 'gray',
                    }),
                ToggleColumn::make('is_active')
                    ->label('Aktif'),
                TextColumn::make('last_sent_at')
                    ->label('Terakhir Dikirim')
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