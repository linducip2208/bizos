<?php

namespace App\Filament\Resources\SodRules\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SodRuleTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('risk_level', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Aturan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sensitive_function')
                    ->label('Fungsi Sensitif')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'buat-pr' => 'Buat PR',
                        'approve-pr' => 'Approve PR',
                        'buat-po' => 'Buat PO',
                        'approve-po' => 'Approve PO',
                        'terima-barang' => 'Terima Barang',
                        'vendor-master' => 'Master Vendor',
                        'buat-invoice' => 'Buat Invoice',
                        'approve-invoice' => 'Approve Invoice',
                        'buat-journal' => 'Buat Journal',
                        'approve-journal' => 'Approve Journal',
                        'payment-run' => 'Payment Run',
                        'approve-payment' => 'Approve Payment',
                        'bank-reconciliation' => 'Bank Rec',
                        'input-payroll' => 'Input Payroll',
                        'approve-payroll' => 'Approve Payroll',
                        'employee-master' => 'Master Karyawan',
                        'manage-role' => 'Manage Role',
                        'manage-user' => 'Manage User',
                        default => $state,
                    }),
                TextColumn::make('conflicting_function')
                    ->label('Fungsi Konflik')
                    ->badge()
                    ->color('danger')
                    ->formatStateUsing(fn($state) => match($state) {
                        'buat-po' => 'Buat PO',
                        'approve-po' => 'Approve PO',
                        'terima-barang' => 'Terima Barang',
                        'vendor-master' => 'Master Vendor',
                        'approve-invoice' => 'Approve Invoice',
                        'approve-journal' => 'Approve Journal',
                        'bank-reconciliation' => 'Bank Rec',
                        'approve-payroll' => 'Approve Payroll',
                        'manage-user' => 'Manage User',
                        default => $state,
                    }),
                TextColumn::make('risk_level')
                    ->label('Risiko')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'critical' => 'danger',
                        'high' => 'warning',
                        'medium' => 'info',
                        'low' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color('gray'),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                IconColumn::make('is_system_default')
                    ->label('Default')
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