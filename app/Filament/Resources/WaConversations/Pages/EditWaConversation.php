<?php

namespace App\Filament\Resources\WaConversations\Pages;

use App\Filament\Resources\WaConversations\WaConversationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWaConversation extends EditRecord
{
    protected static string $resource = WaConversationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
