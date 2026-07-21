<?php

namespace App\Filament\Resources\WasteRecords\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WasteRecordsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('record_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('waste_type')
                    ->label('Jenis')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'hazardous' => 'B3',
                        'solid' => 'Padat',
                        'liquid' => 'Cair',
                        'organic' => 'Organik',
                        'recyclable' => 'Daur Ulang',
                        'electronic' => 'Elektronik',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'hazardous' => 'danger',
                        'solid' => 'gray',
                        'liquid' => 'info',
                        'organic' => 'success',
                        'recyclable' => 'success',
                        'electronic' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('quantity_kg')
                    ->label('Jumlah (kg)')
                    ->numeric(2)
                    ->sortable(),
                TextColumn::make('disposal_method')
                    ->label('Metode')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'landfill' => 'TPA',
                        'incinerated' => 'Insinerasi',
                        'recycled' => 'Daur Ulang',
                        'composted' => 'Kompos',
                        'treated_offsite' => 'Pihak Ketiga',
                        default => $state,
                    })
                    ->badge(),
                TextColumn::make('source')
                    ->label('Sumber')
                    ->searchable(),
                TextColumn::make('disposal_cost')
                    ->label('Biaya')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable(),
            ])
            ->defaultSort('record_date', 'desc')
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