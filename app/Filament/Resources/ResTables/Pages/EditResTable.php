<?php

namespace App\Filament\Resources\ResTables\Pages;

use App\Filament\Resources\ResTables\ResTableResource;
use Filament\Resources\Pages\EditRecord;

class EditResTable extends EditRecord
{
    protected static string $resource = ResTableResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
