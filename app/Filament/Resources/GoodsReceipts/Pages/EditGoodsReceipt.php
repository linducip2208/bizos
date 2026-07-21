<?php

namespace App\Filament\Resources\GoodsReceipts\Pages;

use App\Filament\Resources\GoodsReceipts\GoodsReceiptResource;
use App\Services\FinancialIntegrationService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditGoodsReceipt extends EditRecord
{
    protected static string $resource = GoodsReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('buatInvoiceVendor')
                ->label('Buat Invoice Vendor')
                ->icon('heroicon-o-document-currency-dollar')
                ->color('success')
                ->visible(fn() => $this->record->status === 'posted')
                ->requiresConfirmation()
                ->modalHeading('Buat Invoice Vendor dari GRN')
                ->modalDescription(fn() => "Buat invoice vendor dari GRN {$this->record->grn_number}?")
                ->action(function () {
                    try {
                        $service = app(FinancialIntegrationService::class);
                        $invoice = $service->createInvoiceFromGrn($this->record);

                        Notification::make()
                            ->title('Invoice vendor berhasil dibuat')
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