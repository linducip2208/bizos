<?php

namespace App\Filament\Resources\SalesReturns\Pages;

use App\Filament\Resources\SalesReturns\SalesReturnResource;
use App\Services\SalesService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSalesReturn extends EditRecord
{
    protected static string $resource = SalesReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('prosesReturn')
                ->label('Proses Return')
                ->icon('heroicon-o-check-circle')
                ->color('warning')
                ->visible(fn() => $this->record->status === 'draft')
                ->requiresConfirmation()
                ->action(function () {
                    try {
                        app(SalesService::class)->processReturn($this->record);
                        Notification::make()
                            ->title('Return berhasil diproses')
                            ->success()
                            ->send();
                        $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));
                    } catch (\Exception $e) {
                        Notification::make()->title('Gagal')->body($e->getMessage())->danger()->send();
                    }
                }),

            Action::make('refundReturn')
                ->label('Refund')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->visible(fn() => $this->record->status === 'received')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => 'refunded']);
                    Notification::make()->title('Return telah direfund')->success()->send();
                    $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));
                }),

            Action::make('voidReturn')
                ->label('Batalkan Return')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn() => in_array($this->record->status, ['draft', 'received']))
                ->requiresConfirmation()
                ->action(function () {
                    try {
                        app(SalesService::class)->voidReturn($this->record);
                        Notification::make()->title('Return dibatalkan')->success()->send();
                        $this->redirect($this->getResource()::getUrl('index'));
                    } catch (\Exception $e) {
                        Notification::make()->title('Gagal')->body($e->getMessage())->danger()->send();
                    }
                }),

            DeleteAction::make(),
        ];
    }
}
