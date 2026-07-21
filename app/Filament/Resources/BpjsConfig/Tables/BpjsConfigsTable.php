<?php

namespace App\Filament\Resources\BpjsConfig\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BpjsConfigsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->label('Perusahaan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('bpjs_type')
                    ->label('Tipe BPJS')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('company_rate')
                    ->label('Rate Perusahaan (%)')
                    ->numeric(2)
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('employee_rate')
                    ->label('Rate Karyawan (%)')
                    ->numeric(2)
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('max_salary_cap')
                    ->label('Batas Gaji Maks.')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('effective_year')
                    ->label('Tahun Berlaku')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
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