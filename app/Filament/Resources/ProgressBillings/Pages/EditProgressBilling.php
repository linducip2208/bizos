<?php

namespace App\Filament\Resources\ProgressBillings\Pages;

use App\Filament\Resources\ProgressBillings\ProgressBillingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProgressBilling extends EditRecord
{
    protected static string $resource = ProgressBillingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}