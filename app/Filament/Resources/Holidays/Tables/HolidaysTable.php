<?php

namespace App\Filament\Resources\Holidays\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HolidaysTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Hari Libur')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'national' => 'Nasional',
                        'company' => 'Perusahaan',
                        'religious' => 'Keagamaan',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'national' => 'danger',
                        'company' => 'primary',
                        'religious' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('year')
                    ->label('Tahun')
                    ->sortable()
                    ->placeholder('Berulang'),
                IconColumn::make('is_recurring')
                    ->label('Berulang')
                    ->boolean(),
            ])
            ->defaultSort('date', 'asc')
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
