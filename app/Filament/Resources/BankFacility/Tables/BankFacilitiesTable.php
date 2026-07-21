<?php

namespace App\Filament\Resources\BankFacility\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BankFacilitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('bank_name')
                    ->label('Bank')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('name')
                    ->label('Fasilitas')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('facility_type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'overdraft' => 'Overdraft',
                        'term_loan' => 'Term Loan',
                        'revolving_credit' => 'Revolving',
                        'lc' => 'L/C',
                        'bank_guarantee' => 'BG',
                        'factoring' => 'Factoring',
                        'supply_chain_finance' => 'SCF',
                        'other' => 'Lainnya',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('limit_amount')
                    ->label('Limit')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('utilized_amount')
                    ->label('Terpakai')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('available_amount')
                    ->label('Tersedia')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('utilization_percent')
                    ->label('Utilisasi')
                    ->state(fn($record) => $record->getUtilizationPercent() . '%')
                    ->color(fn($record) => $record->getUtilizationPercent() > 80 ? 'danger' : ($record->getUtilizationPercent() > 60 ? 'warning' : 'success'))
                    ->sortable(query: fn($q, $d) => $q->orderByRaw('(utilized_amount / NULLIF(limit_amount, 0)) ' . $d)),
                TextColumn::make('interest_rate_percent')
                    ->label('Bunga')
                    ->suffix('% p.a.')
                    ->sortable(),
                TextColumn::make('expiry_date')
                    ->label('Berakhir')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'expired' => 'gray',
                        'cancelled' => 'danger',
                        'suspended' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'active' => 'Aktif',
                        'expired' => 'Kadaluarsa',
                        'cancelled' => 'Dibatalkan',
                        'suspended' => 'Ditangguhkan',
                        default => $state,
                    }),
                IconColumn::make('is_secured')
                    ->label('Jaminan')
                    ->boolean(),
            ])
            ->defaultSort('bank_name', 'asc')
            ->filters([
                SelectFilter::make('facility_type')
                    ->label('Tipe')
                    ->options([
                        'overdraft' => 'Overdraft',
                        'term_loan' => 'Term Loan',
                        'revolving_credit' => 'Revolving Credit',
                        'lc' => 'L/C',
                        'bank_guarantee' => 'Bank Garansi',
                    ]),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktif',
                        'expired' => 'Kadaluarsa',
                        'cancelled' => 'Dibatalkan',
                        'suspended' => 'Ditangguhkan',
                    ]),
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