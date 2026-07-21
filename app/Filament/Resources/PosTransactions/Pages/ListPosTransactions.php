<?php

namespace App\Filament\Resources\PosTransactions\Pages;

use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\PosTransactions\PosTransactionResource;
use Filament\Resources\Pages\ListRecords;

class ListPosTransactions extends ListRecords
{
    use HasExcelExport;

    protected static string $resource = PosTransactionResource::class;
}