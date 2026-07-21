<?php

namespace App\Filament\Resources\PosTransactions\Pages;

use App\Filament\Resources\PosTransactions\PosTransactionResource;
use App\Services\FinancialIntegrationService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPosTransaction extends EditRecord
{
    protected static string $resource = PosTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('postingJurnal')
                ->label('Posting Jurnal')
                ->icon('heroicon-o-document-check')
                ->color('success')
                ->visible(fn() => $this->record->payment_status === 'paid')
                ->requiresConfirmation()
                ->modalHeading('Posting Jurnal Transaksi POS')
                ->modalDescription(fn() => "Posting jurnal untuk transaksi {$this->record->receipt_number}? Total: Rp " . number_format($this->record->grand_total, 2, ',', '.'))
                ->action(function () {
                    try {
                        $service = app(FinancialIntegrationService::class);
                        $journal = $service->postPosTransactionToJournal($this->record);

                        Notification::make()
                            ->title('Jurnal berhasil diposting')
                            ->body("Jurnal {$journal->journal_number} telah dibuat.")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal posting jurnal')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            DeleteAction::make(),
        ];
    }
}