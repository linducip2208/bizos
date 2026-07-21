<?php

namespace App\Filament\Resources\EcommerceOrder\Tables;

use App\Services\EcommercePosBridgeService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EcommerceOrderTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('channel.channel_name')
                    ->label('Channel')
                    ->formatStateUsing(fn($state) => match($state) {
                        'shopee' => 'Shopee',
                        'tokopedia' => 'Tokopedia',
                        'bukalapak' => 'Bukalapak',
                        'tiktok_shop' => 'TikTok Shop',
                        'lazada' => 'Lazada',
                        'blibli' => 'Blibli',
                        default => $state,
                    })
                    ->badge()
                    ->sortable(),
                TextColumn::make('channel_order_id')
                    ->label('ID Pesanan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order_date')
                    ->label('Tanggal')
                    ->date('d M Y, H:i')
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->label('Pelanggan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->numeric('Rp')
                    ->sortable()
                    ->money('IDR'),
                TextColumn::make('channel_status')
                    ->label('Status Channel')
                    ->formatStateUsing(fn($state) => match($state) {
                        'unpaid' => 'Belum Bayar',
                        'paid' => 'Sudah Bayar',
                        'shipped' => 'Dikirim',
                        'delivered' => 'Terkirim',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'unpaid' => 'warning',
                        'paid' => 'info',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('sync_status')
                    ->label('Sinkron')
                    ->formatStateUsing(fn($state) => match($state) {
                        'pending' => 'Menunggu',
                        'synced' => 'Tersinkron',
                        'failed' => 'Gagal',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'pending' => 'warning',
                        'synced' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                    TextColumn::make('shipping_method')
                    ->label('Kurir')
                    ->sortable(),
                TextColumn::make('posTransaction.receipt_number')
                    ->label('Transaksi POS')
                    ->placeholder('-')
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('sync_to_pos')
                    ->label('Sinkron ke POS')
                    ->icon(Heroicon::OutlinedArrowsRightLeft)
                    ->color('primary')
                    ->visible(fn($record) => !$record->pos_transaction_id || $record->sync_status !== 'synced')
                    ->action(function ($record) {
                        try {
                            $posTx = app(EcommercePosBridgeService::class)->syncOrderToPos($record);
                            Notification::make()
                                ->title('Berhasil Sinkron ke POS')
                                ->body('Transaksi POS #' . $posTx->receipt_number . ' dibuat.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal Sinkron')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('sync_inventory')
                    ->label('Update Stok')
                    ->icon(Heroicon::OutlinedCubeTransparent)
                    ->color('warning')
                    ->visible(fn($record) => $record->sync_status === 'synced')
                    ->action(function ($record) {
                        try {
                            app(EcommercePosBridgeService::class)->syncInventoryAfterOrder($record);
                            Notification::make()
                                ->title('Stok Diperbarui')
                                ->body('Stok dikurangi sesuai pesanan e-commerce.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Update Stok')
                    ->modalDescription('Kurangi stok produk sesuai pesanan ini?'),
                Action::make('refund')
                    ->label('Retur / Refund')
                    ->icon(Heroicon::OutlinedArrowUturnLeft)
                    ->color('danger')
                    ->visible(fn($record) => $record->sync_status === 'synced' && !$record->pos_refund_id)
                    ->action(function ($record) {
                        try {
                            $refund = app(EcommercePosBridgeService::class)->syncRefund($record);
                            Notification::make()
                                ->title('Refund Dibuat')
                                ->body('Refund #' . $refund->refund_number . ' dibuat. Stok dikembalikan.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Gagal Refund')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Refund Pesanan')
                    ->modalDescription('Refund pesanan ini? Stok akan dikembalikan ke gudang.'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}