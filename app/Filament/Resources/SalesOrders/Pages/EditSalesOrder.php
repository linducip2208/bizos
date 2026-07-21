<?php

namespace App\Filament\Resources\SalesOrders\Pages;

use App\Filament\Resources\SalesInvoices\SalesInvoiceResource;
use App\Filament\Resources\SalesOrders\SalesOrderResource;
use App\Services\SalesService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSalesOrder extends EditRecord
{
    protected static string $resource = SalesOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('buatInvoice')
                ->label('Buat Invoice')
                ->icon('heroicon-o-document-currency-dollar')
                ->color('success')
                ->visible(fn() => !in_array($this->record->status, ['draft', 'cancelled', 'invoiced']))
                ->requiresConfirmation()
                ->modalHeading('Buat Sales Invoice')
                ->modalDescription(fn() => "Buat invoice dari SO {$this->record->so_number}?")
                ->action(function () {
                    try {
                        $service = app(SalesService::class);
                        $invoice = $service->createInvoiceFromOrder($this->record);

                        Notification::make()
                            ->title('Invoice berhasil dibuat')
                            ->body("Invoice {$invoice->invoice_number} telah dibuat.")
                            ->success()
                            ->send();

                        $this->redirect(SalesInvoiceResource::getUrl('edit', ['record' => $invoice->id]));
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
