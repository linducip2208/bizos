<?php

namespace App\Filament\Resources\Quotations\Pages;

use App\Filament\Resources\Quotations\QuotationResource;
use App\Filament\Resources\SalesOrders\SalesOrderResource;
use App\Services\SalesService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditQuotation extends EditRecord
{
    protected static string $resource = QuotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('konversiKeOrder')
                ->label('Konversi ke Sales Order')
                ->icon('heroicon-o-arrows-right-left')
                ->color('success')
                ->visible(fn() => $this->record->status === 'accepted')
                ->requiresConfirmation()
                ->modalHeading('Konversi Quotation ke Sales Order')
                ->modalDescription(fn() => "Buat Sales Order dari quotation {$this->record->quotation_number}?")
                ->action(function () {
                    try {
                        $service = app(SalesService::class);
                        $order = $service->convertQuotationToOrder($this->record);

                        Notification::make()
                            ->title('Sales Order berhasil dibuat')
                            ->body("SO {$order->so_number} telah dibuat dari quotation.")
                            ->success()
                            ->send();

                        $this->redirect(SalesOrderResource::getUrl('edit', ['record' => $order->id]));
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal konversi')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            DeleteAction::make(),
        ];
    }
}
