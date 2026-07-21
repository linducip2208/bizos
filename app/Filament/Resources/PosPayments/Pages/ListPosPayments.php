<?php

namespace App\Filament\Resources\PosPayments\Pages;

use App\Filament\Resources\PosPayments\PosPaymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPosPayments extends ListRecords
{
    protected static string $resource = PosPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}