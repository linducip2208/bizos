<?php

namespace App\Filament\Resources\EcommerceChannel\Pages;

use App\Filament\Resources\EcommerceChannel\EcommerceChannelResource;
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