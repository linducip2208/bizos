<?php

namespace App\Filament\Resources\Leaves\Pages;

use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\Leaves\LeaveResource;
use Filament\Resources\Pages\ListRecords;

class ListLeaves extends ListRecords
{
    use HasExcelExport;

    protected static string $resource = LeaveResource::class;
}
