<?php

namespace App\Filament\Resources\HotelServices\Pages;

use App\Filament\Concerns\HasBulkActions;
use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\HotelServices\HotelServiceResource;
use Filament\Resources\Pages\ListRecords;

class ListHotelServices extends ListRecords
{
    use HasBulkActions;
    use HasExcelExport;

    protected static string $resource = HotelServiceResource::class;
}
