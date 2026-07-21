<?php

namespace App\Filament\Resources\AssetDepreciation\Pages;

use App\Filament\Resources\AssetDepreciation\AssetDepreciationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAssetDepreciation extends EditRecord
{
    protected static string $resource = AssetDepreciationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}