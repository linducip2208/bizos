<?php

namespace App\Filament\Resources\Webhooks\Tables;

use App\Services\WebhookService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WebhooksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('event')
                    ->label('Event')
                    ->badge()
                    ->color('indigo')
                    ->sortable(),
                TextColumn::make('url')
                    ->label('Target URL')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->url),
                TextColumn::make('deliveries_count')
                    ->label('Delivery')
                    ->counts('deliveries')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('test')
                    ->label('Test')
                    ->icon('heroicon-o-play')
                    ->color('warning')
                    ->action(function ($record) {
                        $webhookService = app(WebhookService::class);
                        $delivery = $webhookService->testWebhook($record);

                        if ($delivery->status === 'success') {
                            \Filament\Notifications\Notification::make()
                                ->title('Test Berhasil')
                                ->body("Response: HTTP {$delivery->response_code} ({$delivery->duration_ms}ms)")
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Test Gagal')
                                ->body($delivery->error_message ?? 'Unknown error')
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('delivery_logs')
                    ->label('Log')
                    ->icon('heroicon-o-list-bullet')
                    ->color('gray')
                    ->url(fn ($record) => route('filament.admin.resources.webhooks.edit', ['record' => $record]))
                    ->openUrlInNewTab(false),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}