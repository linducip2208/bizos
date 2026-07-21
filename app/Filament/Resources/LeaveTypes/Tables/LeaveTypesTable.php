<?php

namespace App\Filament\Resources\LeaveTypes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LeaveTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label('Perusahaan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('default_days')
                    ->label('Hari Default')
                    ->sortable()
                    ->numeric(),
                TextColumn::make('max_days')
                    ->label('Maksimal Hari')
                    ->sortable()
                    ->numeric()
                    ->placeholder('-'),
                IconColumn::make('is_annual')
                    ->label('Tahunan')
                    ->boolean(),
                IconColumn::make('is_paid')
                    ->label('Dibayar')
                    ->boolean(),
                IconColumn::make('require_attachment')
                    ->label('Lampiran')
                    ->boolean(),
                IconColumn::make('require_approval')
                    ->label('Approval')
                    ->boolean(),
                ColorColumn::make('color')
                    ->label('Warna'),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->defaultSort('name', 'asc')
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