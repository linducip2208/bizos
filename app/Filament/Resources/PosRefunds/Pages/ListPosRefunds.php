<?php

namespace App\Filament\Resources\PosRefunds\Pages;

use App\Filament\Resources\PosRefunds\PosRefundResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPosRefunds extends ListRecords
{
    protected static string $resource = PosRefundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
