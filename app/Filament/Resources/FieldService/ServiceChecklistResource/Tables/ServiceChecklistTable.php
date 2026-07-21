<?php

namespace App\Filament\Resources\FieldService\ServiceChecklistResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;

class ServiceChecklistTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Checklist')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('service_type')
                    ->label('Tipe Layanan')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'preventive' => 'Preventif',
                        'corrective' => 'Korektif',
                        'installation' => 'Instalasi',
                        'inspection' => 'Inspeksi',
                        default => $state,
                    }),
                TextColumn::make('items_count')
                    ->label('Jumlah Item')
                    ->counts('items')
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
