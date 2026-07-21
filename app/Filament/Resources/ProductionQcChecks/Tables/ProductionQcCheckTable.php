<?php

namespace App\Filament\Resources\ProductionQcChecks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductionQcCheckTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('productionOrder.po_number')
                    ->label('No. PO')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('check_type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'incoming_material' => 'warning',
                        'in_process' => 'info',
                        'final' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => match ($state) {
                        'incoming_material' => 'Material',
                        'in_process' => 'Proses',
                        'final' => 'Final',
                        default => $state,
                    }),
                TextColumn::make('parameter')
                    ->label('Parameter')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('result')
                    ->label('Hasil')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'pass' => 'success',
                        'fail' => 'danger',
                        'conditional' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => match ($state) {
                        'pass' => 'Lolos',
                        'fail' => 'Gagal',
                        'conditional' => 'Bersyarat',
                        default => '-',
                    }),
                TextColumn::make('checker.name')
                    ->label('Pemeriksa'),
                TextColumn::make('checked_at')
                    ->label('Tgl Periksa')
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