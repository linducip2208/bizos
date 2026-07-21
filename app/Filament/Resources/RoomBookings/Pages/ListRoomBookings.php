<?php

namespace App\Filament\Resources\RoomBookings\Pages;

use App\Filament\Concerns\HasBulkActions;
use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\RoomBookings\RoomBookingResource;
use Filament\Resources\Pages\ListRecords;

class ListRoomBookings extends ListRecords
{
    use HasBulkActions;
    use HasExcelExport;

    protected static string $resource = RoomBookingResource::class;
}