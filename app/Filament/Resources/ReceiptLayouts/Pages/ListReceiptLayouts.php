<?php

namespace App\Filament\Resources\ReceiptLayouts\Pages;

use App\Filament\Resources\ReceiptLayouts\ReceiptLayoutResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReceiptLayouts extends ListRecords
{
    protected static string $resource = ReceiptLayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
