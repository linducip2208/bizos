<?php

namespace App\Filament\Resources\AssetDepreciation\Pages;

use App\Filament\Resources\AssetDepreciation\AssetDepreciationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAssetDepreciations extends ListRecords
{
    protected static string $resource = AssetDepreciationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
