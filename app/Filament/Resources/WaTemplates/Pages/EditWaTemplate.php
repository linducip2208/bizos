<?php

namespace App\Filament\Resources\WaTemplates\Pages;

use App\Filament\Resources\WaTemplates\WaTemplateResource;
use App\Models\WaTemplate;
use App\Services\WhatsappBusinessService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditWaTemplate extends EditRecord
{
    protected static string $resource = WaTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('submit_to_meta')
                ->label('Ajukan ke Meta')
                ->icon('heroicon-o-paper-airplane')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Ajukan Template ke Meta')
                ->modalDescription('Template akan dikirim ke WhatsApp Business API untuk ditinjau.')
                ->modalSubmitActionLabel('Ya, Ajukan')
                ->action(function (WaTemplate $record) {
                    $waService = app(WhatsappBusinessService::class);

                    $components = [
                        [
                            'type' => 'BODY',
                            'text' => $record->content,
                        ],
                    ];

                    if (!empty($record->components)) {
                        $components = $record->components;
                    }

                    $result = $waService->submitTemplate(
                        $record->name,
                        $record->language ?? 'id',
                        $record->category ?? 'marketing',
                        $components
                    );

                    if ($result['success']) {
                        $record->update([
                            'meta_template_id' => $result['meta_template_id'] ?? null,
                            'meta_template_status' => 'pending_approval',
                            'meta_synced_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Template berhasil diajukan ke Meta')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Gagal mengajukan template')
                            ->body($result['message'] ?? 'Error tidak diketahui')
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('check_status')
                ->label('Cek Status Meta')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->action(function (WaTemplate $record) {
                    if (!$record->meta_template_id) {
                        Notification::make()
                            ->title('Template belum diajukan ke Meta')
                            ->warning()
                            ->send();
                        return;
                    }

                    $waService = app(WhatsappBusinessService::class);
                    $waService->checkAndUpdateTemplateStatus($record);
                    $record->refresh();

                    Notification::make()
                        ->title('Status diperbarui')
                        ->body('Status terkini: ' . $record->status_label)
                        ->success()
                        ->send();
                }),

            Action::make('sync_all_from_meta')
                ->label('Sync Semua')
                ->icon('heroicon-o-arrow-path-rounded-square')
                ->color('gray')
                ->action(function () {
                    $waService = app(WhatsappBusinessService::class);
                    $result = $waService->syncTemplates();

                    Notification::make()
                        ->title($result['message'])
                        ->success()
                        ->send();
                }),

            Action::make('preview_template')
                ->label('Preview')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->modalHeading(fn (WaTemplate $record) => 'Preview: ' . $record->name)
                ->modalContent(function (WaTemplate $record) {
                    return view('filament.components.wa-template-preview', [
                        'template' => $record,
                    ]);
                })
                ->modalWidth('lg'),
        ];
    }
}