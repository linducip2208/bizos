<?php

namespace App\Filament\Resources\IntercompanyTransactions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class IntercompanyTransactionTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_number')
                    ->label('No. Referensi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fromCompany.name')
                    ->label('Dari Perusahaan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('toCompany.name')
                    ->label('Ke Perusahaan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('transaction_type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'sale' => 'Penjualan',
                        'purchase' => 'Pembelian',
                        'transfer' => 'Transfer',
                        'payment' => 'Pembayaran',
                        'expense_allocation' => 'Alokasi Biaya',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('transaction_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('currency.code')
                    ->label('Mata Uang')
                    ->sortable(),
                TextColumn::make('exchange_rate')
                    ->label('Kurs')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'pending_approval' => 'warning',
                        'approved' => 'info',
                        'completed' => 'success',
                        'rejected' => 'danger',
                        'void' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('approver.name')
                    ->label('Disetujui Oleh')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'pending_approval' => 'Pending Approval',
                        'approved' => 'Approved',
                        'completed' => 'Completed',
                        'rejected' => 'Rejected',
                        'void' => 'Void',
                    ]),
                SelectFilter::make('transaction_type')
                    ->label('Tipe')
                    ->options([
                        'sale' => 'Penjualan',
                        'purchase' => 'Pembelian',
                        'transfer' => 'Transfer',
                        'payment' => 'Pembayaran',
                        'expense_allocation' => 'Alokasi Biaya',
                    ]),
                Filter::make('transaction_date')
                    ->form([
                        DatePicker::make('from')->label('Dari'),
                        DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn($q, $d) => $q->whereDate('transaction_date', '>=', $d))
                            ->when($data['until'], fn($q, $d) => $q->whereDate('transaction_date', '<=', $d));
                    }),
                SelectFilter::make('from_company_id')
                    ->label('Dari Perusahaan')
                    ->relationship('fromCompany', 'name'),
                SelectFilter::make('to_company_id')
                    ->label('Ke Perusahaan')
                    ->relationship('toCompany', 'name'),
            ], layout: FiltersLayout::AboveContentCollapsible)
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
