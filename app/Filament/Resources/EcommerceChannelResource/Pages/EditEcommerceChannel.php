<?php

namespace App\Filament\Resources\EcommerceChannelResource\Pages;

use App\Filament\Resources\EcommerceChannelResource\EcommerceChannelResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEcommerceChannel extends EditRecord
{
    protected static string $resource = EcommerceChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
