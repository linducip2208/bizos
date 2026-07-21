<?php

namespace App\Filament\Resources\ClientSegments\Pages;

use App\Filament\Resources\ClientSegments\ClientSegmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditClientSegment extends EditRecord
{
    protected static string $resource = ClientSegmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
