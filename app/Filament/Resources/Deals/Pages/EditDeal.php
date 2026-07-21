<?php

namespace App\Filament\Resources\Deals\Pages;

use App\Filament\Resources\Deals\DealResource;
use App\Filament\Resources\SalesOrders\SalesOrderResource;
use App\Services\FinancialIntegrationService;
use App\Services\SalesService;
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
            Action::make('buatSalesOrder')
                ->label('Buat Sales Order')
                ->icon('heroicon-o-shopping-cart')
                ->color('primary')
                ->visible(fn() => $this->record->status === 'menang')
                ->requiresConfirmation()
                ->modalHeading('Buat Sales Order dari Deal')
                ->modalDescription(fn() => "Buat Sales Order dari deal \"{$this->record->title}\"?")
                ->action(function () {
                    try {
                        $service = app(SalesService::class);
                        $order = $service->createOrderFromDeal($this->record);

                        Notification::make()
                            ->title('Sales Order berhasil dibuat')
                            ->body("SO {$order->so_number} telah dibuat.")
                            ->success()
                            ->send();

                        $this->redirect(SalesOrderResource::getUrl('edit', ['record' => $order->id]));
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal membuat Sales Order')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('buatInvoice')
                ->label('Buat Invoice')
                ->icon('heroicon-o-document-currency-dollar')
                ->color('success')
                ->visible(fn() => $this->record->status === 'menang')
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