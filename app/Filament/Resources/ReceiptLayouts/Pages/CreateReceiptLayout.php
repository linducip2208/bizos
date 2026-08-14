<?php

namespace App\Filament\Resources\ReceiptLayouts\Pages;

use App\Filament\Resources\ReceiptLayouts\ReceiptLayoutResource;
use Filament\Resources\Pages\CreateRecord;

class CreateReceiptLayout extends CreateRecord
{
    protected static string $resource = ReceiptLayoutResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = auth()->user()->company_id;

        return $data;
    }
}
