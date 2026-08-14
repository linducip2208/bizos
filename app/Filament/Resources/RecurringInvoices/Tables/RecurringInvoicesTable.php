<?php

namespace App\Filament\Resources\RecurringInvoices\Tables;

use App\Services\RecurringBillingService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RecurringInvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('client.name')
                    ->label('Klien')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('frequency')
                    ->label('Frekuensi')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'daily' => 'Harian',
                        'weekly' => 'Mingguan',
                        'monthly' => 'Bulanan',
                        'quarterly' => 'Kuartalan',
                        'yearly' => 'Tahunan',
                        default => $state,
                    }),
                TextColumn::make('interval')
                    ->label('Interval')
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('next_run_date')
                    ->label('Jadwal Berikutnya')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('last_generated_at')
                    ->label('Terakhir Dibuat')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Belum pernah')
                    ->sortable(),
                IconColumn::make('auto_send')
                    ->label('Kirim Otomatis')
                    ->boolean(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'active' => 'Aktif',
                        'paused' => 'Ditunda',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'active' => 'success',
                        'paused' => 'warning',
                        'completed' => 'info',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('next_run_date', 'asc')
            ->recordActions([
                EditAction::make(),
                Action::make('generate_now')
                    ->label('Generate Sekarang')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->hidden(fn ($record) => $record->status !== 'active')
                    ->action(function ($record) {
                        $invoice = $record->generateInvoice();

                        Notification::make()
                            ->title('Invoice berhasil dibuat')
                            ->body("Invoice #{$invoice->invoice_number} berhasil dibuat.")
                            ->success()
                            ->send();
                    }),
                Action::make('pause')
                    ->label('Tunda')
                    ->icon('heroicon-o-pause')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->hidden(fn ($record) => $record->status !== 'active')
                    ->action(function ($record) {
                        app(RecurringBillingService::class)->pause($record->id);

                        Notification::make()
                            ->title('Invoice berulang ditunda')
                            ->success()
                            ->send();
                    }),
                Action::make('resume')
                    ->label('Lanjutkan')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->hidden(fn ($record) => $record->status !== 'paused')
                    ->action(function ($record) {
                        app(RecurringBillingService::class)->resume($record->id);

                        Notification::make()
                            ->title('Invoice berulang dilanjutkan')
                            ->success()
                            ->send();
                    }),
                Action::make('cancel')
                    ->label('Batalkan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->hidden(fn ($record) => in_array($record->status, ['cancelled', 'completed']))
                    ->action(function ($record) {
                        app(RecurringBillingService::class)->cancel($record->id);

                        Notification::make()
                            ->title('Invoice berulang dibatalkan')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
