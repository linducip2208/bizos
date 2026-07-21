<?php

namespace App\Filament\Resources\PosTransactionItems\Pages;

use App\Filament\Resources\PosTransactionItems\PosTransactionItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPosTransactionItems extends ListRecords
{
    protected static string $resource = PosTransactionItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}