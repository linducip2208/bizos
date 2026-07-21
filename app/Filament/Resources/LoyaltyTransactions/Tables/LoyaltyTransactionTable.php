<?php

namespace App\Filament\Resources\LoyaltyTransactions\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LoyaltyTransactionTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('member.name')
                    ->label('Member')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('member.member_code')
                    ->label('Kode Member')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'earn' => 'success',
                        'redeem' => 'warning',
                        'expire' => 'danger',
                        'adjust' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'earn' => 'Perolehan',
                        'redeem' => 'Penukaran',
                        'expire' => 'Kadaluarsa',
                        'adjust' => 'Penyesuaian',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('points')
                    ->label('Poin')
                    ->sortable()
                    ->color(fn ($record) => in_array($record->type, ['redeem', 'expire']) ? 'danger' : 'success'),
                TextColumn::make('transaction.receipt_number')
                    ->label('No. Transaksi')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('member_id')
                    ->label('Member')
                    ->relationship('member', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'earn' => 'Perolehan',
                        'redeem' => 'Penukaran',
                        'expire' => 'Kadaluarsa',
                        'adjust' => 'Penyesuaian',
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
