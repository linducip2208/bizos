<?php

namespace App\Filament\Resources\QualityChecks\Pages;

use App\Filament\Resources\QualityChecks\QualityCheckResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditQualityCheck extends EditRecord
{
    protected static string $resource = QualityCheckResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
