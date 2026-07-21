<?php

namespace App\Filament\Resources\TicketCategory\Pages;

use App\Filament\Resources\TicketCategory\TicketCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTicketCategory extends CreateRecord
{
    protected static string $resource = TicketCategoryResource::class;
}