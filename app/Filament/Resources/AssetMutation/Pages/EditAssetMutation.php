<?php

namespace App\Filament\Resources\AssetMutation\Pages;

use App\Filament\Resources\AssetMutation\AssetMutationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAssetMutation extends EditRecord
{
    protected static string $resource = AssetMutationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}