<?php

namespace App\Filament\Resources\BpmnProcess\Pages;

use App\Filament\Resources\BpmnProcess\BpmnProcessResource;
use Filament\Resources\Pages\ListRecords;

class ListBpmnProcesses extends ListRecords
{
    protected static string $resource = BpmnProcessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
            \Filament\Actions\Action::make('loadPrebuilt')
                ->label('Muat Template')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    app(\App\Services\BpmnService::class)->getPrebuiltProcesses();
                    \Filament\Notifications\Notification::make()
                        ->title('Template BPMN berhasil dimuat')
                        ->success()
                        ->send();
                }),
        ];
    }
}