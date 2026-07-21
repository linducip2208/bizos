<?php

namespace App\Filament\Resources\RabItems\Pages;

use App\Filament\Concerns\HasBulkActions;
use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\RabItems\RabItemResource;
use Filament\Resources\Pages\ListRecords;

class ListRabItems extends ListRecords
{
    use HasBulkActions;
    use HasExcelExport;

    protected static string $resource = RabItemResource::class;
}
