<?php

namespace App\Filament\Resources\WaAutoReplies\Pages;

use App\Filament\Resources\WaAutoReplies\WaAutoReplyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWaAutoReply extends EditRecord
{
    protected static string $resource = WaAutoReplyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
