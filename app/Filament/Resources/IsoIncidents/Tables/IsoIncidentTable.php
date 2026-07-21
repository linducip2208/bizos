<?php

namespace App\Filament\Resources\IsoIncidents\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IsoIncidentTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('detected_at', 'desc')
            ->columns([
                TextColumn::make('incident_number')
                    ->label('No')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('incident_type')
                    ->label('Tipe')
                    ->badge(),
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
                IconColumn::make('reportable_to_regulator')
                    ->label('Lapor')
                    ->boolean(),
                TextColumn::make('detected_at')
                    ->label('Terdeteksi')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('reporter.name')
                    ->label('Pelapor'),
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
