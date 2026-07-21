<?php

namespace App\Filament\Resources\Rooms\Pages;

use App\Filament\Concerns\HasBulkActions;
use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\Rooms\RoomResource;
use Filament\Resources\Pages\ListRecords;

class ListRooms extends ListRecords
{
    use HasBulkActions;
    use HasExcelExport;

    protected static string $resource = RoomResource::class;
}
