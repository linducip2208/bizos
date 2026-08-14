<?php

namespace App\Filament\Resources\ThreeWayMatches\Pages;

use App\Filament\Resources\ThreeWayMatches\ThreeWayMatchResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditThreeWayMatch extends EditRecord
{
    protected static string $resource = ThreeWayMatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('rerun_match')
                ->label('Jalankan Ulang Matching')
                ->icon('heroicon-o-arrows-right-left')
                ->color('warning')
                ->action(function () {
                    $record = $this->getRecord();
                    $service = app(\App\Services\ThreeWayMatchService::class);
                    $results = $service->performMatch(
                        $record->purchaseOrder,
                        $record->goodsReceipt,
                        $record->invoice
                    );
                    $record->update(array_merge($results, [
                        'matched_by' => auth()->id(),
                        'matched_at' => now(),
                    ]));
                    $this->fillForm();
                })
                ->requiresConfirmation()
                ->modalHeading('Jalankan Ulang Matching')
                ->modalDescription('Akan menjalankan ulang pencocokan 3 arah dan memperbarui hasil.')
                ->modalSubmitActionLabel('Jalankan'),
            DeleteAction::make(),
        ];
    }
}
