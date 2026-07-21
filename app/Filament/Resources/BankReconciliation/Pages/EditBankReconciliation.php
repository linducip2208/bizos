<?php

namespace App\Filament\Resources\BankReconciliation\Pages;

use App\Filament\Resources\BankReconciliation\BankReconciliationResource;
use App\Services\BankReconciliationService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditBankReconciliation extends EditRecord
{
    protected static string $resource = BankReconciliationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('auto_match')
                ->label('Auto Match')
                ->icon('heroicon-o-arrows-right-left')
                ->color('info')
                ->action(function () {
                    $service = app(BankReconciliationService::class);
                    $result = $service->autoMatch($this->record);
                    $this->record->update(['status' => 'in_progress']);
                    Notification::make()
                        ->title('Auto Match Selesai')
                        ->body("{$result['matched']} cocok, {$result['unmatched_journal']} jurnal tidak cocok, {$result['unmatched_bank']} bank tidak cocok")
                        ->success()
                        ->send();
                })
                ->visible(fn () => in_array($this->record->status, ['draft', 'in_progress'])),
            Action::make('complete')
                ->label('Selesaikan Rekonsiliasi')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->action(function () {
                    $service = app(BankReconciliationService::class);
                    $service->calculateDifference($this->record);
                    $service->completeReconciliation($this->record);
                    Notification::make()
                        ->title('Rekonsiliasi Selesai')
                        ->success()
                        ->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                })
                ->requiresConfirmation()
                ->visible(fn () => in_array($this->record->status, ['draft', 'in_progress'])),
            DeleteAction::make(),
        ];
    }
}