<?php

namespace App\Filament\Resources\IntercompanyTransactions\Pages;

use App\Filament\Resources\IntercompanyTransactions\IntercompanyTransactionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateIntercompanyTransaction extends CreateRecord
{
    protected static string $resource = IntercompanyTransactionResource::class;
}
