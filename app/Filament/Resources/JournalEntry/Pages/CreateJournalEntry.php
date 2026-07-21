<?php

namespace App\Filament\Resources\JournalEntry\Pages;

use App\Filament\Resources\JournalEntry\JournalEntryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateJournalEntry extends CreateRecord
{
    protected static string $resource = JournalEntryResource::class;
}