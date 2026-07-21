<?php

namespace App\Filament\Resources\FieldService\ServiceContractResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServiceContractTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('contract_number')
                    ->label('No Kontrak')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('client.name')
                    ->label('Klien')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contract_type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'maintenance_regular' => 'Maintenance Reguler',
                        'maintenance_comprehensive' => 'Maintenance Komprehensif',
                        'installation' => 'Instalasi',
                        'repair' => 'Perbaikan',
                        default => $state,
                    }),
                TextColumn::make('billing_amount')
                    ->label('Tagihan')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('billing_cycle')
                    ->label('Siklus')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'monthly' => 'Bulanan',
                        'quarterly' => 'Kuartalan',
                        'annually' => 'Tahunan',
                        default => $state,
                    }),
                TextColumn::make('service_frequency')
                    ->label('Frekuensi')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'weekly' => 'Mingguan',
                        'biweekly' => '2 Minggu',
                        'monthly' => 'Bulanan',
                        'quarterly' => 'Kuartalan',
                        default => $state,
                    }),
                TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Berakhir')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'draft' => 'gray',
                        'suspended' => 'warning',
                        'expired' => 'danger',
                        'terminated' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'active' => 'Aktif',
                        'suspended' => 'Ditangguhkan',
                        'expired' => 'Kadaluarsa',
                        'terminated' => 'Dihentikan',
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