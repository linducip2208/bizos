<?php

namespace App\Filament\Resources\FieldService\ContractedEquipmentResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContractedEquipmentTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('equipment_name')
                    ->label('Nama Peralatan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('serviceContract.contract_number')
                    ->label('No Kontrak')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('brand')
                    ->label('Merek')
                    ->searchable(),
                TextColumn::make('model')
                    ->label('Model')
                    ->searchable(),
                TextColumn::make('serial_number')
                    ->label('No Seri')
                    ->searchable(),
                TextColumn::make('location')
                    ->label('Lokasi')
                    ->searchable(),
                TextColumn::make('next_service_date')
                    ->label('Service Berikutnya')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('last_service_date')
                    ->label('Service Terakhir')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'under_repair' => 'warning',
                        'decommissioned' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Aktif',
                        'under_repair' => 'Dalam Perbaikan',
                        'decommissioned' => 'Nonaktif',
                        default => $state,
                    }),
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