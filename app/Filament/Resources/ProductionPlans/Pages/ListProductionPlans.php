<?php

namespace App\Filament\Resources\ProductionPlans\Pages;

use App\Filament\Resources\ProductionPlans\ProductionPlanResource;
use Filament\Resources\Pages\ListRecords;

class ListProductionPlans extends ListRecords
{
    protected static string $resource = ProductionPlanResource::class;
}
