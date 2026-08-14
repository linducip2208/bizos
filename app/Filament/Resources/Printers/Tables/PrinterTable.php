<?php

namespace App\Filament\Resources\Printers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PrinterTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('printer_type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'thermal_58' => 'Thermal 58',
                        'thermal_80' => 'Thermal 80',
                        'a4' => 'A4',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'thermal_58' => 'info',
                        'thermal_80' => 'warning',
                        'a4' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('connection_type')
                    ->label('Koneksi')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'usb' => 'USB',
                        'network' => 'Network',
                        'cloud' => 'Cloud',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'usb' => 'gray',
                        'network' => 'success',
                        'cloud' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('ip_address')
                    ->label('IP')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->placeholder('Semua')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'active' => 'Aktif',
                        'inactive' => 'Nonaktif',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    }),
                IconColumn::make('is_default')
                    ->label('Utama')
                    ->boolean(),
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
