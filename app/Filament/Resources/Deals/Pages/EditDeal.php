<?php

namespace App\Filament\Resources\Deals\Pages;

use App\Filament\Resources\Deals\DealResource;
use App\Services\FinancialIntegrationService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditDeal extends EditRecord
{
    protected static string $resource = DealResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('buatInvoice')
                ->label('Buat Invoice')
                ->icon('heroicon-o-document-currency-dollar')
                ->color('success')
                ->visible(fn() => $this->record->status === 'won')
                ->requiresConfirmation()
                ->modalHeading('Buat Invoice dari Deal')
                ->modalDescription(fn() => "Buat invoice dari deal \"{$this->record->title}\"? Nilai: Rp " . number_format($this->record->expected_value, 2, ',', '.'))
                ->action(function () {
                    try {
                        $service = app(FinancialIntegrationService::class);
                        $invoice = $service->createInvoiceFromDeal($this->record);

                        Notification::make()
                            ->title('Invoice berhasil dibuat')
                            ->body("Invoice {$invoice->invoice_number} telah dibuat.")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal membuat invoice')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            DeleteAction::make(),
        ];
    }
}