<?php

namespace App\Filament\Resources\GuestFolios\Pages;

use App\Filament\Concerns\HasBulkActions;
use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\GuestFolios\GuestFolioResource;
use Filament\Resources\Pages\ListRecords;

class ListGuestFolios extends ListRecords
{
    use HasBulkActions;
    use HasExcelExport;

    protected static string $resource = GuestFolioResource::class;
}
