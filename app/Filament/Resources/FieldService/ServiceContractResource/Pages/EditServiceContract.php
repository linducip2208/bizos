<?php

namespace App\Filament\Resources\FieldService\ServiceContractResource\Pages;

use App\Filament\Resources\FieldService\ServiceContractResource\ServiceContractResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use App\Services\FieldServiceService;

class EditServiceContract extends EditRecord
{
    protected static string $resource = ServiceContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate_wo')
                ->label('Generate Work Order')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('success')
                ->action(function () {
                    $service = app(FieldServiceService::class);
                    $results = $service->generateScheduledWorkOrders();
                    Notification::make()
                        ->title("WO dibuat: {$results['created']}, dilewati: {$results['skipped']}")
                        ->success()
                        ->send();
                }),
        ];
    }
}
