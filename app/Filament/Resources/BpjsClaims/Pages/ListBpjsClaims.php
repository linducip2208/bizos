<?php

namespace App\Filament\Resources\BpjsClaims\Pages;

use App\Filament\Resources\BpjsClaims\BpjsClaimResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBpjsClaims extends ListRecords
{
    protected static string $resource = BpjsClaimResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}