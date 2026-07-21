<?php

namespace App\Filament\Resources\Webhooks\Pages;

use App\Filament\Resources\Webhooks\WebhookResource;
use App\Models\WebhookDelivery;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextEntry;
use Filament\Schemas\Components\RepeatableEntry;
use Filament\Schemas\Schema;
use Filament\Resources\Pages\EditRecord;
use App\Services\WebhookService;

class EditWebhook extends EditRecord
{
    protected static string $resource = WebhookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('test')
                ->label('Test Webhook')
                ->icon('heroicon-o-play')
                ->color('warning')
                ->action(function () {
                    $webhookService = app(WebhookService::class);
                    $delivery = $webhookService->testWebhook($this->record);

                    if ($delivery->status === 'success') {
                        \Filament\Notifications\Notification::make()
                            ->title('Test Berhasil')
                            ->body("HTTP {$delivery->response_code} ({$delivery->duration_ms}ms)")
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
            DeleteAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Riwayat Delivery')
                    ->schema([
                        RepeatableEntry::make('deliveries')
                            ->label('')
                            ->schema([
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn ($state) => match ($state) {
                                        'success' => 'success',
                                        'failed' => 'danger',
                                        default => 'gray',
                                    }),
                                TextEntry::make('response_code')
                                    ->label('HTTP Code'),
                                TextEntry::make('duration_ms')
                                    ->label('Durasi')
                                    ->suffix(' ms'),
                                TextEntry::make('attempt')
                                    ->label('Attempt'),
                                TextEntry::make('error_message')
                                    ->label('Error')
                                    ->placeholder('-'),
                                TextEntry::make('created_at')
                                    ->label('Waktu')
                                    ->dateTime('d M Y H:i:s'),
                            ])
                            ->columns(6),
                    ])
                    ->collapsible(),
            ]);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['headers']) && is_string($data['headers'])) {
            $data['headers'] = json_decode($data['headers'], true);
        }

        return $data;
    }
}
