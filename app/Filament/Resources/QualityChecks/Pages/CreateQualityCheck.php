<?php

namespace App\Filament\Resources\QualityChecks\Pages;

use App\Filament\Resources\QualityChecks\QualityCheckResource;
use Filament\Resources\Pages\CreateRecord;

class CreateQualityCheck extends CreateRecord
{
    protected static string $resource = QualityCheckResource::class;
}
