<?php

namespace App\Filament\Resources\Promotions\Pages;

use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\Promotions\PromotionResource;
use Filament\Resources\Pages\ListRecords;

class ListPromotions extends ListRecords
{
    use HasExcelExport;

    protected static string $resource = PromotionResource::class;
}
