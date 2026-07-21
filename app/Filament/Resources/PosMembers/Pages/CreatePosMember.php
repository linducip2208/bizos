<?php

namespace App\Filament\Resources\PosMembers\Pages;

use App\Filament\Resources\PosMembers\PosMemberResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePosMember extends CreateRecord
{
    protected static string $resource = PosMemberResource::class;
}