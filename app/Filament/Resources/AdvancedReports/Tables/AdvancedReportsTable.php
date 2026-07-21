<?php

namespace App\Filament\Resources\AdvancedReports\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AdvancedReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Laporan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pivot' => 'Pivot Table',
                        'crosstab' => 'Cross Tab',
                        'chart_combo' => 'Chart Combo',
                        'table' => 'Tabel',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'pivot' => 'indigo',
                        'crosstab' => 'violet',
                        'chart_combo' => 'emerald',
                        'table' => 'slate',
                        default => 'gray',
                    }),
                TextColumn::make('company.name')
                    ->label('Perusahaan')
                    ->searchable(),
                TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->searchable(),
                TextColumn::make('is_public')
                    ->label('Publik')
                    ->badge()
                    ->formatStateUsing(fn(bool $state): string => $state ? 'Publik' : 'Private')
                    ->color(fn(bool $state): string => $state ? 'success' : 'gray'),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'pivot' => 'Pivot Table',
                        'crosstab' => 'Cross Tab',
                        'chart_combo' => 'Chart Combo',
                        'table' => 'Tabel',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}