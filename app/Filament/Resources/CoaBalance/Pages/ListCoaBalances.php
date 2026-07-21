<?php

namespace App\Filament\Resources\CoaBalance\Pages;

use App\Filament\Resources\CoaBalance\CoaBalanceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCoaBalances extends ListRecords
{
    protected static string $resource = CoaBalanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
