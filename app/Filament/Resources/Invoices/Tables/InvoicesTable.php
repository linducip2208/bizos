<?php

namespace App\Filament\Resources\Invoices\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Js;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Nomor Faktur')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('invoice_type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'sales' => 'Penjualan',
                        'purchase' => 'Pembelian',
                        'service' => 'Jasa',
                        'other' => 'Lainnya',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'sales' => 'success',
                        'purchase' => 'warning',
                        'service' => 'info',
                        'other' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('invoice_date')
                    ->label('Tanggal Faktur')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('total')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('paid_amount')
                    ->label('Dibayar')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'draft' => 'Draft',
                        'sent' => 'Terkirim',
                        'partial' => 'Sebagian',
                        'paid' => 'Lunas',
                        'overdue' => 'Terlambat',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'draft' => 'gray',
                        'sent' => 'info',
                        'partial' => 'warning',
                        'paid' => 'success',
                        'overdue' => 'danger',
                        'cancelled' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('payment_token')
                    ->label('Link Pembayaran')
                    ->badge()
                    ->formatStateUsing(fn ($record) => match (true) {
                        $record->status === 'paid' => 'Lunas',
                        ! $record->payment_token => 'Belum dibuat',
                        $record->isPaymentLinkExpired() => 'Kedaluwarsa',
                        default => 'Aktif',
                    })
                    ->color(fn ($record) => match (true) {
                        $record->status === 'paid' => 'success',
                        ! $record->payment_token => 'gray',
                        $record->isPaymentLinkExpired() => 'danger',
                        default => 'info',
                    }),
            ])
            ->defaultSort('invoice_date', 'desc')
            ->recordActions([
                Action::make('copy_payment_link')
                    ->label('Salin Link Pembayaran')
                    ->icon('heroicon-o-link')
                    ->color('primary')
                    ->action(function ($record, $livewire) {
                        $record->generatePaymentToken();

                        $url = $record->getPaymentLinkUrl();
                        $livewire->js('window.navigator.clipboard.writeText('.Js::from($url).')');

                        Notification::make()
                            ->title('Link pembayaran disalin')
                            ->body($url)
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
