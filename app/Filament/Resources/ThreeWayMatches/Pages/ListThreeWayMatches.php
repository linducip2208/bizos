<?php

namespace App\Filament\Resources\ThreeWayMatches\Pages;

use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\ThreeWayMatches\ThreeWayMatchResource;
use Filament\Resources\Pages\ListRecords;

class ListThreeWayMatches extends ListRecords
{
    use HasExcelExport;

    protected static string $resource = ThreeWayMatchResource::class;
}
