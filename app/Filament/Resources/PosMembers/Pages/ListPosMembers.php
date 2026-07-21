<?php

namespace App\Filament\Resources\PosMembers\Pages;

use App\Filament\Resources\PosMembers\PosMemberResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPosMembers extends ListRecords
{
    protected static string $resource = PosMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}