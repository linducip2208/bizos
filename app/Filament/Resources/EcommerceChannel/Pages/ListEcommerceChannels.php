<?php

namespace App\Filament\Resources\EcommerceChannel\Pages;

use App\Filament\Resources\EcommerceChannel\EcommerceChannelResource;
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