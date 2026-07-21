<?php

namespace App\Filament\Resources\WaConversations\Pages;

use App\Filament\Resources\WaConversations\WaConversationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWaConversations extends ListRecords
{
    protected static string $resource = WaConversationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
