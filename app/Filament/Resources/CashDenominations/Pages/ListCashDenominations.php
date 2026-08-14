<?php

namespace App\Filament\Resources\CashDenominations\Pages;

use App\Filament\Resources\CashDenominations\CashDenominationResource;
use App\Services\CashDenominationService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListCashDenominations extends ListRecords
{
    protected static string $resource = CashDenominationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('seed')
                ->label('Muat Pecahan Default')
                ->icon('heroicon-o-banknotes')
                ->color('gray')
                ->action(function () {
                    $companyId = session('current_company_id') ?? auth()->user()?->company_id;

                    if (!$companyId) {
                        Notification::make()
                            ->title('Perusahaan tidak ditemukan untuk user aktif.')
                            ->danger()
                            ->send();

                        return;
                    }

                    app(CashDenominationService::class)->seedDefaultDenominations((int) $companyId);

                    Notification::make()
                        ->title('Pecahan default berhasil dimuat.')
                        ->success()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}
