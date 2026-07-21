<?php

namespace App\Filament\Resources\WaAutoReplies\Pages;

use App\Filament\Resources\WaAutoReplies\WaAutoReplyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWaAutoReplies extends ListRecords
{
    protected static string $resource = WaAutoReplyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}