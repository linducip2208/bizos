<?php

namespace App\Filament\Resources\IsoPolicies\Pages;

use App\Filament\Resources\IsoPolicies\IsoPolicyResource;
use Filament\Resources\Pages\EditRecord;

class EditIsoPolicy extends EditRecord
{
    protected static string $resource = IsoPolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
