<?php

namespace App\Filament\Resources\FinishedGoods\Pages;

use App\Filament\Resources\FinishedGoods\FinishedGoodResource;
use Filament\Resources\Pages\ListRecords;

class ListFinishedGoods extends ListRecords
{
    protected static string $resource = FinishedGoodResource::class;
}
