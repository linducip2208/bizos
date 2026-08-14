<?php

namespace App\Filament\Resources\CashierShifts\Pages;

use App\Filament\Resources\CashierShifts\CashierShiftResource;
use App\Services\CashDenominationService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCashierShift extends EditRecord
{
    protected static string $resource = CashierShiftResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $service = app(CashDenominationService::class);
        $shiftId = $this->record->id;

        if (empty($data['expected_cash'])) {
            $data['expected_cash'] = $service->calculateExpectedCash($shiftId);
        }

        $data['actual_cash'] = $service->calculateActualCash($shiftId);
        $data['difference'] = round((float) $data['actual_cash'] - (float) $data['expected_cash'], 2);

        return $data;
    }
}