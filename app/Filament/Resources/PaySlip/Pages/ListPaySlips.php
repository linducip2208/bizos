<?php

namespace App\Filament\Resources\PaySlip\Pages;

use App\Filament\Resources\PaySlip\PaySlipResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPaySlips extends ListRecords
{
    protected static string $resource = PaySlipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
