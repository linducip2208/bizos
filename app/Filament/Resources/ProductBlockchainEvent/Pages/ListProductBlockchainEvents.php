<?php

namespace App\Filament\Resources\ProductBlockchainEvent\Pages;

use App\Filament\Resources\ProductBlockchainEvent\ProductBlockchainEventResource;
use Filament\Resources\Pages\ListRecords;

class ListProductBlockchainEvents extends ListRecords
{
    protected static string $resource = ProductBlockchainEventResource::class;
}