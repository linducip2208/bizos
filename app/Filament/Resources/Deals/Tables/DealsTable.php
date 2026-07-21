<?php

namespace App\Filament\Resources\Deals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DealsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('lead.first_name')
                    ->label('Lead')
                    ->searchable(),
                TextColumn::make('client.name')
                    ->label('Klien')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('stage.name')
                    ->label('Tahap')
                    ->sortable(),
                TextColumn::make('expected_value')
                    ->label('Nilai Estimasi')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'terbuka' => 'warning',
                        'menang' => 'success',
                        'kalah' => 'danger',
                        'tertunda' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'terbuka' => 'Terbuka',
                        'menang' => 'Menang',
                        'kalah' => 'Kalah',
                        'tertunda' => 'Tertunda',
                        default => $state,
                    }),
                TextColumn::make('expected_close_date')
                    ->label('Target Tutup')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('assignedTo.first_name')
                    ->label('Ditugaskan')
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
