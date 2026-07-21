<?php

namespace App\Filament\Resources\EcommerceChannelResource\Pages;

use App\Filament\Resources\EcommerceChannelResource\EcommerceChannelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEcommerceChannels extends ListRecords
{
    protected static string $resource = EcommerceChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Tambah Channel')];
    }
}
