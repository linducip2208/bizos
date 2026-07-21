<?php

namespace App\Filament\Resources\Investments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InvestmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'deposit' => 'Deposito',
                        'bond' => 'Obligasi',
                        'mutual_fund' => 'Reksadana',
                        'stock' => 'Saham',
                        'government_bond' => 'SBN',
                        'corporate_bond' => 'Obligasi Korp',
                        'money_market' => 'Pasar Uang',
                        'other' => 'Lainnya',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('institution')
                    ->label('Institusi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('principal_amount')
                    ->label('Pokok')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('current_value')
                    ->label('Nilai Saat Ini')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('interest_rate_percent')
                    ->label('Bunga')
                    ->suffix('% p.a.')
                    ->sortable(),
                TextColumn::make('maturity_date')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'matured' => 'info',
                        'liquidated' => 'gray',
                        'impaired' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'active' => 'Aktif',
                        'matured' => 'Jatuh Tempo',
                        'liquidated' => 'Dilikuidasi',
                        'impaired' => 'Menurun',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('total_interest_earned')
                    ->label('Bunga Diperoleh')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->defaultSort('start_date', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'deposit' => 'Deposito',
                        'bond' => 'Obligasi',
                        'mutual_fund' => 'Reksadana',
                        'stock' => 'Saham',
                        'government_bond' => 'SBN',
                        'other' => 'Lainnya',
                    ]),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktif',
                        'matured' => 'Jatuh Tempo',
                        'liquidated' => 'Dilikuidasi',
                        'impaired' => 'Menurun',
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