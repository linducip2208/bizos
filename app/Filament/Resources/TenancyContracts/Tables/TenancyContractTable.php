<?php

namespace App\Filament\Resources\TenancyContracts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TenancyContractTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('contract_number')
                    ->label('Kontrak')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('propertyUnit.unit_number')
                    ->label('Unit')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('client.name')
                    ->label('Penyewa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('monthly_rent')
                    ->label('Sewa/Bulan')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Selesai')
                    ->date('d M Y')
                    ->sortable(),
                IconColumn::make('renewal_option')
                    ->label('Perpanjangan')
                    ->boolean(),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray' => 'draft',
                        'success' => 'active',
                        'warning' => 'expiring_soon',
                        'danger' => 'expired',
                        'info' => 'terminated',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'active' => 'Aktif',
                        'expiring_soon' => 'Segera Habis',
                        'expired' => 'Habis',
                        'terminated' => 'Dihentikan',
                        default => $state,
                    }),
            ])
            ->defaultSort('created_at', 'desc')
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
