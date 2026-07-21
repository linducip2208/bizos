<?php

namespace App\Filament\Resources\CallLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CallLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('phone_number')
                    ->label('No. Telepon')
                    ->searchable(),
                TextColumn::make('lead.email')
                    ->label('Lead')
                    ->searchable(),
                TextColumn::make('client.name')
                    ->label('Klien')
                    ->searchable(),
                TextColumn::make('direction')
                    ->label('Arah')
                    ->badge()
                    ->color(fn(string $state): string => $state === 'inbound' ? 'info' : 'success')
                    ->formatStateUsing(fn(string $state): string => $state === 'inbound' ? 'Masuk' : 'Keluar'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'planned' => 'gray', 'completed' => 'success',
                        'missed' => 'danger', 'voicemail' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('duration_seconds')
                    ->label('Durasi')
                    ->formatStateUsing(fn($state) => $state ? gmdate('i:s', $state) : '-'),
                TextColumn::make('employee.first_name')
                    ->label('Karyawan'),
                TextColumn::make('scheduled_at')
                    ->label('Dijadwalkan')
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
