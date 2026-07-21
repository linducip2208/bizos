<?php

namespace App\Filament\Resources\LeadActivities\Pages;

use App\Filament\Resources\LeadActivities\LeadActivityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLeadActivities extends ListRecords
{
    protected static string $resource = LeadActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
