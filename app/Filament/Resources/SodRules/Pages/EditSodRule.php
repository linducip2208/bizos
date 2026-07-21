<?php

namespace App\Filament\Resources\SodRules\Pages;

use App\Filament\Resources\SodRules\SodRuleResource;
use Filament\Resources\Pages\EditRecord;

class EditSodRule extends EditRecord
{
    protected static string $resource = SodRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}