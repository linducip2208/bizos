<?php

namespace App\Filament\Resources\Bids\Pages;

use App\Filament\Resources\Bids\BidResource;
use Filament\Resources\Pages\ListRecords;

class ListBids extends ListRecords
{
    protected static string $resource = BidResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
