<?php

namespace App\Filament\Resources\Budget\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BudgetsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label('Perusahaan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama Anggaran')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fiscal_year')
                    ->label('Tahun Fiskal')
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label('Tgl. Mulai')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Tgl. Selesai')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('department.name')
                    ->label('Departemen')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->searchable()
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