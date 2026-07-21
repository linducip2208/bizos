<?php

namespace App\Filament\Resources\AttendanceConfigs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttendanceConfigsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label('Perusahaan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('method')
                    ->label('Metode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('gps_radius_meters')
                    ->label('Radius GPS')
                    ->suffix(' m')
                    ->sortable()
                    ->numeric(),
                IconColumn::make('require_selfie')
                    ->label('Selfie')
                    ->boolean(),
            ])
            ->defaultSort('company_id', 'asc')
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
