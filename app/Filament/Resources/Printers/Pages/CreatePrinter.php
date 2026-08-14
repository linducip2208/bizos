<?php

namespace App\Filament\Resources\Printers\Pages;

use App\Filament\Resources\Printers\PrinterResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePrinter extends CreateRecord
{
    protected static string $resource = PrinterResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = auth()->user()->company_id;
        $data['created_by'] = auth()->user()->id;

        return $data;
    }
}
